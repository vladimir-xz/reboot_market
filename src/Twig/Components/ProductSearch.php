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

    public function __construct(private ProductRepository $productRepository, private CategoryRepository $categoryRepository, private LoggerInterface $logger)
    {
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

        if ($wasEmpty && $this->filters) {
            $this->emit('makeFiltered', [
                'newValue' => true,
            ]);
        } elseif (!$wasEmpty && empty($this->filters)) {
            $this->emit('makeFiltered', [
                'newValue' => false,
            ]);
        }

        $this->sendCategoriesForTree();
    }

    private function sendCategoriesForTree()
    {
        // if ($this->includedCategories) {
        //     $categories['active'] = $this->productRepository->getCategoriesFromSearch($this->query, $this->includedCategories, $this->excludedCategories, $this->filters);
        //     $categories['chosen'] = $this->includedCategories;
        // } else {
        //     $categories['neutral'] = $this->productRepository->getCategoriesFromSearch($this->query, $this->includedCategories, $this->excludedCategories, $this->filters);
        // }
        // $this->emit('redraw', [
        //     'newCatalogs' => $categories,
        // ]);
        // $result = $this->productRepository->getPaginatedValues($this->query, $this->includedCategories, $this->excludedCategories, $this->filters, $this->page);
        // $this->maxNbPages = $result->getNbPages();

        $allRecords = $this->productRepository->getCategoriesFromSearch($this->query, $this->includedCategories, $this->excludedCategories, $this->filters);
        $collection = new ArrayCollection($allRecords);
        $map = $collection->reduce(function (array $accumulator, $record) {
            $count = $accumulator['count'] ?? 0;
            $company = $record->getBrand();
            $price = $record->getPrice();
            $type = $record->getType();
            $specs = $record->getSpecifications();
            $categoryId = $record->getCategory()->getId();
            $currentMax = $accumulator['price']['max'] ?? 0;
            $currentMin = $accumulator['price']['min'] ?? 0;

            $accumulator['brand'][$company] = $company;
            $accumulator['type'][$type] = $type;
            $accumulator['categories'][$categoryId] = $categoryId;
            if ($currentMax < $price && $currentMin === 0) {
                $accumulator['price']['max'] = $price;
                $accumulator['price']['min'] = $currentMax;
            } elseif ($currentMax < $price) {
                $accumulator['price']['max'] = $price;
            } elseif ($currentMin === 0 || $currentMin > $price) {
                $accumulator['price']['min'] = $price;
            }

            foreach ($specs as $spec) {
                $property = $spec->getProperty();
                $propValue = $spec->getValue();
                $accumulator[$property][$propValue] = $propValue;
            }
            $count++;
            $accumulator['count'] = $count;

            return $accumulator;
        }, []);

        if ($this->includedCategories) {
            $this->logger->info('this is active:');
            $this->logger->info(print_r($map['categories'], true));
            $categories['active'] = $map['categories'];
            $this->logger->info('this is chosen:');
            $this->logger->info(print_r($this->includedCategories, true));
            $categories['chosen'] = $this->includedCategories;
            $this->logger->info('this is exluded:');
            $this->logger->info(print_r($this->excludedCategories, true));
            $categories['excluded'] = $this->excludedCategories;
        } else {
            $categories['neutral'] = $map['categories'];
            $categories['excluded'] = $this->excludedCategories;
        }
        $maxNbPages = ceil($map['count'] / 12);


        $this->emit('redraw', [
            'newCatalogs' => $categories,
        ]);

        // TODO: assign explicitly the perPage amount
        $this->dispatchBrowserEvent('product:update', ['max' => $maxNbPages]);
    }

    public function getProducts()
    {
        return $this->productRepository->getPaginatedValues($this->query, $this->includedCategories, $this->excludedCategories, $this->filters, $this->page);
    }
}
