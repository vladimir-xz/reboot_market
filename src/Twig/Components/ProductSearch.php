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
use Symfony\UX\LiveComponent\Attribute\PreReRender;
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

    public int $nextPage = 2;
    #[LiveProp]
    public string $query = '';
    #[LiveProp(writable: true, url: new UrlMapping(as: 'i'))]
    public array $includedCategories = [];
    #[LiveProp(writable: true, url: new UrlMapping(as: 'f'))]
    public array $filters = [];
    #[LiveProp(writable: true, url: new UrlMapping(as: 'e'))]
    public array $excludedCategories = [];

    public function __construct(
        private ProductRepository $productRepository,
        private LoggerInterface $logger,
        private CatalogHandler $catalogHandler,
    ) {
    }

    #[PreReRender]
    public function sendCategoriesForTree()
    {
        if (
            $this->query === '' &&
            empty($this->includedCategories) &&
            empty($this->filters) &&
            empty($this->excludedCategories)
        ) {
            $this->dispatchBrowserEvent('catalog:renew', [
                'treeMap' => [],
            ]);
            return;
        }

        $allRecords = $this->productRepository->getAllProductsWithCategoryAndFilters($this->query, $this->includedCategories, $this->excludedCategories, $this->filters);
        $result = $this->catalogHandler->prepareNewCatalogsForDrawing($allRecords, $this->includedCategories, $this->excludedCategories);

        $this->dispatchBrowserEvent('catalog:renew', [
            'treeMap' => $result['mappedCatalogs'],
        ]);

        $this->dispatchBrowserEvent('product:updateFilters', [
            'filters' => $result['mappedRecords']
        ]);
    }

    #[LiveListener('receiveQuery')]
    public function receiveQuery(#[LiveArg] string $query)
    {
        $this->query = $query;
    }

    // TODO: change values in catalog class
    #[LiveListener('removeIncluded')]
    public function removeIncludedCategories()
    {
        $this->includedCategories = [];
    }

    // TODO: change values in catalog class
    #[LiveListener('removeExcluded')]
    public function removeExcludedCategories()
    {
        $this->excludedCategories = [];
    }

    #[LiveListener('removeFilters')]
    public function removeFiltersCategories()
    {
        $this->filters = [];
        $this->dispatchBrowserEvent('productFilters:remove');
    }

    public function updateCategoriesAndLabels(array $newCategories)
    {
        if (empty($newCategories['i']) && $this->includedCategories) {
            $this->emit('changeIfIncluded', [
                'newValue' => false,
            ]);
        } elseif ($newCategories['i'] && empty($this->includedCategories)) {
            $this->emit('changeIfIncluded', [
                'newValue' => true,
            ]);
        }
        $this->includedCategories = $newCategories['i'];

        if (empty($newCategories['e']) && $this->excludedCategories) {
            $this->emit('changeIfExcluded', [
                'newValue' => false,
            ]);
        } elseif ($newCategories['e'] && empty($this->excludedCategories)) {
            $this->emit('changeIfExcluded', [
                'newValue' => true,
            ]);
        }
        $this->excludedCategories = $newCategories['e'];
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

        $ifNowEmpty = empty($this->filters);
        if ($wasEmpty !== $ifNowEmpty) {
            $this->emit('makeFiltered', [
                'newValue' => !$ifNowEmpty,
            ]);
        }
    }

    #[LiveAction]
    public function revertCategories(#[LiveArg] int $newId)
    {
        $result = $this->catalogHandler->revertCategories($newId, $this->includedCategories, $this->excludedCategories);

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
        return $this->productRepository->getPaginatedValues($this->query, $this->includedCategories, $this->excludedCategories, $this->filters);
    }
}
