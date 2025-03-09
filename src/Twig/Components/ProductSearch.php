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
use App\Service\CatalogHandler;
use Pagerfanta\Pagerfanta;
use Psr\Log\LoggerInterface;

#[AsLiveComponent]
class ProductSearch extends AbstractController
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    #[LiveProp]
    public int $page = 1;
    #[LiveProp]
    public string $query = '';
    #[LiveProp(writable: true, url: new UrlMapping(as: 'i'))]
    public array $includedCategories = [];
    #[LiveProp(writable: true, url: new UrlMapping(as: 'f'))]
    public array $filters = [];
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
        private CatalogHandler $catalogHandler,
    ) {
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

    public function updateCategoriesAndLabels(array $newCategories)
    {
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
        $this->logger->warning(print_r($newCategories['included'], true));
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
        $hydr = http_build_query(['i' => $this->includedCategories]);
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


        $treeMap = $this->catalogHandler->prepareNewCatalogsForDrawing($categories);

        $this->dispatchBrowserEvent('catalog:renew', [
            'treeMap' => $treeMap,
        ]);

        $this->dispatchBrowserEvent('product:updateFilters', [
            'filters' => $map
        ]);

        // TODO: assign explicitly the perPage amount
        $this->dispatchBrowserEvent('product:update', ['max' => $maxNbPages]);
    }

    #[LiveAction]
    public function revertCategories(#[LiveArg] int $newId)
    {
        $result = $this->catalogHandler->revertCategories($newId, $this->includedCategories, $this->excludedCategories);

        $this->logger->info('Here we have a revert result: ' . print_r($result, true));
        $this->updateCategoriesAndLabels($result);
    }

    #[LiveAction]
    public function excludeCategories(#[LiveArg] int $newId)
    {
        $result = $this->catalogHandler->excludeCategories($newId, $this->includedCategories, $this->excludedCategories);

        $this->updateCategoriesAndLabels($result);
    }

    #[LiveAction]
    public function includeCategories(#[LiveArg] int $newId)
    {
        $result = $this->catalogHandler->includeCategories($newId, $this->includedCategories, $this->excludedCategories);

        $this->updateCategoriesAndLabels($result);
    }

    public function getProducts()
    {
        return $this->productRepository->getPaginatedValues($this->query, $this->includedCategories, $this->excludedCategories, $this->filters, $this->page);
    }

    public function hydrateCatalog(array $url)
    {
        return http_build_query(['i' => $url], '', '&', PHP_QUERY_RFC3986);
    }

    public function dehydrateCatalog($url)
    {
        return http_build_query(['i' => $url], '', '&', PHP_QUERY_RFC3986);
    }
}
