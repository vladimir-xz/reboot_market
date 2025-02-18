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
        private LoggerInterface $logger
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

    #[LiveListener('receiveCategories')]
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
        $this->logger->info(print_r($this->filters, true));
        $map = $this->mapAllRecords->mapRecords($allRecords, true);

        if ($this->includedCategories) {
            $categories['active'] = $map['categories'] ?? [];
            $categories['included'] = $this->includedCategories;
            $categories['excluded'] = $this->excludedCategories;
        } else {
            $this->logger->info('This is map value: ');
            $this->logger->info(print_r($map, true));
            $categories['neutral'] = $map['categories'] ?? [];
            $categories['excluded'] = $this->excludedCategories;
        }
        $count = $map['count'] ?? 1;
        $this->logger->info($count);
        $maxNbPages = ceil($count / 12);


        $this->emit('redraw', [
            'newCatalogs' => $categories,
        ]);
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
}
