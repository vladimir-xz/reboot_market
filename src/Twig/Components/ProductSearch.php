<?php

namespace App\Twig\Components;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Metadata\UrlMapping;
use App\Service\MapAllRecords;
use App\Service\CatalogBuilder;
use Pagerfanta\Pagerfanta;
use Psr\Log\LoggerInterface;

#[AsLiveComponent]
class ProductSearch extends AbstractController
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    private array $catalog;
    private array $children;
    private array $parents;
    #[LiveProp]
    public int $page = 1;
    #[LiveProp]
    public string $query = '';
    #[LiveProp(writable: true, url: new UrlMapping(as: 'i'))]
    public array $includedCategories = [];
    #[LiveProp(writable: true, url: new UrlMapping(as: 'f'))]
    public array $filters = [];
    #[LiveProp(writable: true, url: new UrlMapping(as: 'c'))]
    public array $lastNodesChosen = [];
    #[LiveProp(writable: true, url: new UrlMapping(as: 'c'))]
    public array $lastNodesExcluded = [];
    #[LiveProp(writable: true, url: new UrlMapping(as: 'e'))]
    public array $excludedCategories = [];
    #[LiveProp]
    public int $maxNbPages = 1;

    // #[LiveProp(writable: true, url: new UrlMapping(as: 't'))]
    // public array $types = [];
    // #[LiveProp(writable: true, url: new UrlMapping(as: 's'))]
    // public array $specs = [];

    public function __construct(
        private ProductRepository $productRepository,
        private MapAllRecords $mapAllRecords,
        private LoggerInterface $logger,
        CategoryRepository $categoryRepository,
        CatalogBuilder $builder,
    ) {
        $rawArr = $categoryRepository->getRawTree();
        $result = $builder->build($rawArr);

        $this->catalog = $result['catalog'];
        $this->children = $result['lastChildren'];
        $this->parents = $result['parents'];
    }

    #[LiveListener('receiveQuery')]
    public function receiveQuery(#[LiveArg] string $query)
    {
        $this->query = $query;
        $this->sendCategoriesForTree();
    }

    // TODO: change values in catalog class
    #[LiveListener('removeIncluded')]
    public function removeIncludedCategories()
    {
        $this->includedCategories = [];
        $this->sendCategoriesForTree();
    }

    // TODO: change values in catalog class
    #[LiveListener('removeExcluded')]
    public function removeExcludedCategories()
    {
        $this->excludedCategories = [];
        $this->sendCategoriesForTree();
    }

    #[LiveListener('removeFilters')]
    public function removeFiltersCategories()
    {
        $this->filters = [];
        $this->dispatchBrowserEvent('productFilters:remove');
        $this->sendCategoriesForTree();
    }

    public function receiveCategories(#[LiveArg] array $newCategories)
    {
        $this->logger->info('receiving categories');
        if (array_key_exists('included', $newCategories)) {
            if (empty($newCategories['included']) && $this->includedCategories) {
                $this->emit('changeIfIncluded', [
                    'newValue' => false,
                ]);
            } elseif ($newCategories['included'] && empty($this->includedCategories)) {
                $this->emit('changeIfIncluded', [
                    'newValue' => true,
                ]);
            }
            $this->includedCategories = $newCategories['included'];
        }
        if (array_key_exists('excluded', $newCategories)) {
            if (empty($newCategories['excluded']) && $this->excludedCategories) {
                $this->emit('changeIfExcluded', [
                    'newValue' => false,
                ]);
            } elseif ($newCategories['excluded'] && empty($this->excludedCategories)) {
                $this->emit('changeIfExcluded', [
                    'newValue' => true,
                ]);
            }
            $this->excludedCategories = $newCategories['excluded'];
        }
        // if (!$newCategories) {
        //     $this->excludedCategories = [];
        //     $this->includedCategories = [];
        // }

        $this->sendCategoriesForTree();
    }

    #[LiveAction]
    public function setFilter(
        #[LiveArg] array $newFilters = [],
    ) {
        $wasEmpty = empty($this->filters);
        $filterKey = $newFilters['key'];
        $newValue = $newFilters['value'];
        if (!array_key_exists($filterKey, $this->filters)) {
            $this->filters[$filterKey][$newValue] = $newValue;
        } elseif (array_key_exists($newValue, $this->filters[$filterKey])) {
            unset($this->filters[$filterKey][$newValue]);
            if (empty($this->filters[$filterKey])) {
                unset($this->filters[$filterKey]);
            }
        } else {
            $this->filters[$filterKey][$newValue] = $newValue;
        }

        if ($wasEmpty == $this->filters) {
            $this->emit('makeFiltered', [
                'newValue' => empty($this->filters),
            ]);
        }

        $this->sendCategoriesForTree();
    }

    private function sendCategoriesForTree()
    {
        // TODO: refactor this when mapRecords return []
        $allRecords = $this->productRepository->getAllProductsWithCategoryAndFilters($this->query, $this->includedCategories, $this->excludedCategories, $this->filters);
        $map = $this->mapAllRecords->mapRecords($allRecords, true);

        if ($this->includedCategories) {
            $categories['active'] = $map['categories'] ?? [];
            $categories['included'] = $this->includedCategories;
            $categories['excluded'] = $this->excludedCategories;
        } else {
            $categories['neutral'] = $map['categories'] ?? [];
            $categories['excluded'] = $this->excludedCategories;
        }
        $count = $map['count'] ?? 1;
        $maxNbPages = ceil($count / 12);


        $this->defineActiveCatalogs($categories);
        $this->dispatchBrowserEvent('product:updateFilters', [
            'filters' => $map
        ]);

        // TODO: assign explicitly the perPage amount
        $this->dispatchBrowserEvent('product:update', ['max' => $maxNbPages]);
    }

    public function getProducts()
    {
        return $this->productRepository->getPaginatedValues($this->query, $this->includedCategories, $this->excludedCategories, $this->filters, $this->page);
    }

    public function defineActiveCatalogs(array $newCatalogs = [])
    {
        $alreadyProceededIds = [];
        $this->lastNodesChosen = $newCatalogs['included'] ?? [];
        $this->lastNodesExcluded = $newCatalogs['excluded'] ?? [];
        $allParents = $this->parents;
        $buildMapWithStatuses = function ($ids, $status) use (&$alreadyProceededIds, $allParents) {
            if (!$ids) {
                return [];
            }

            $result = [];
            foreach ($ids as $index => $id) {
                if (array_key_exists($id, $alreadyProceededIds)) {
                    continue;
                }
                $result[$id] = ['isLastNode' => true, 'status' => $status];
                $alreadyProceededIds[$id] = $id;
                while (array_key_exists($id, $allParents)) {
                    $id = $this->parents[$id];

                    if (array_key_exists($id, $alreadyProceededIds)) {
                        break;
                    }
                    $alreadyProceededIds[$id] = $id;
                    $result[$id] = ['isLastNode' => false, 'status' => $status];
                }
            }

            return $result;
        };
        $activeCategories = $buildMapWithStatuses($newCatalogs['active'] ?? [], 'active');
        $includedCategories = $buildMapWithStatuses($newCatalogs['included'] ?? [], 'included');
        $excludedCategories = $buildMapWithStatuses($newCatalogs['excluded'] ?? [], 'excluded');
        $neutralCategories = $buildMapWithStatuses($newCatalogs['neutral'] ?? [], 'neutral');
        // $activeCollection = new ArrayCollection($newCatalogs['active'] ?? []);
        // $activeCategories = $activeCollection->reduce(fn($acc, $index) => $acc + $this->parents[$index], []);

        // $chosenCollection = new ArrayCollection($newCatalogs['chosen'] ?? []);
        // $chosenWithActive = $chosenCollection->reduce(fn($acc, $index) => $acc + $this->parents[$index], []);
        // $chosenCategories = array_diff_key($chosenWithActive, $activeCategories);

        // $excludedCollection = new ArrayCollection($newCatalogs['excluded'] ?? []);
        // $excludedWithActive = $excludedCollection->reduce(fn($acc, $index) => $acc + $this->parents[$index], []);
        // $excludedCategories = array_diff_key($excludedWithActive, $activeCategories, $chosenCategories);

        // $neutralCollection = new ArrayCollection($newCatalogs['neutral'] ?? []);
        // $neutralCategories = $neutralCollection->reduce(fn($acc, $index) => $acc + $this->parents[$index], []);

        $treeMap = $activeCategories + $excludedCategories + $includedCategories + $neutralCategories;

        $this->dispatchBrowserEvent('catalog:renew', [
            'treeMap' => $treeMap,
        ]);
    }

    #[LiveAction]
    public function revertCategories(#[LiveArg] int $newId)
    {
        $lastNodes = $this->children[$newId];

        $collection = new ArrayCollection($lastNodes);
        $ifAnyExistInActive = $collection->exists(fn($key, $value) => array_key_exists($key, $this->lastNodesChosen));
        $ifRevertExclude = array_diff_key($lastNodes, $this->lastNodesExcluded) === [];

        if ($ifRevertExclude) {
            $result['excluded'] = array_diff_key($this->lastNodesExcluded, $lastNodes);
        } elseif ($ifAnyExistInActive) {
            $result['included'] = array_diff_key($this->lastNodesChosen, $lastNodes);
        } else {
            $choosenWithoutExcluded = array_diff_key($lastNodes, $this->lastNodesExcluded);
            $result['included'] = $choosenWithoutExcluded + $this->lastNodesChosen;
        }

        $this->updateLastNodesAndSendResults($result);
    }

    #[LiveAction]
    public function excludeCategories(#[LiveArg] int $newId)
    {
        $lastNodes = $this->children[$newId];


        $ifRevertExclude = array_diff_key($lastNodes, $this->lastNodesExcluded) === [];

        if ($ifRevertExclude) {
            $result['excluded'] = array_diff_key($this->lastNodesExcluded, $lastNodes);
        } else {
            $result['excluded'] = $lastNodes + $this->lastNodesExcluded;
            $result['included'] = array_diff_key($this->lastNodesChosen, $lastNodes);
        }

        $this->updateLastNodesAndSendResults($result);
    }

    #[LiveAction]
    public function includeCategories(#[LiveArg] int $newId)
    {
        $lastNodes = $this->children[$newId];


        $collection = new ArrayCollection($lastNodes);
        $ifAllExistInActive = !$collection->exists(fn($key, $value) => !array_key_exists($key, $this->lastNodesChosen));

        if ($ifAllExistInActive) {
            $result['included'] = array_diff_key($this->lastNodesChosen, $lastNodes);
        } else {
            $result['excluded'] = array_diff_key($this->lastNodesExcluded, $lastNodes);
            $result['included'] = $this->lastNodesChosen + $lastNodes;
        }

        $this->updateLastNodesAndSendResults($result);
    }

    private function updateLastNodesAndSendResults(array $result)
    {
        if (array_key_exists('included', $result)) {
            $this->lastNodesChosen = $result['included'];
        }
        if (array_key_exists('excluded', $result)) {
            $this->lastNodesExcluded = $result['excluded'];
        }

        // $this->dispatchBrowserEvent('catalog:loadProducts');

        $this->receiveCategories($result);
    }
}
