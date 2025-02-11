<?php

namespace App\Twig\Components;

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

    #[LiveProp(writable: true, url: new UrlMapping(as: 'p'))]
    public int $page = 1;
    #[LiveProp]
    public string $query = '';
    #[LiveProp]
    public array $includedCategories = [];
    #[LiveProp(writable: true, url: new UrlMapping(as: 'f'))]
    public array $filters = [];
    #[LiveProp]
    public array $excludedCategories = [];
    // #[LiveProp(writable: true, url: new UrlMapping(as: 't'))]
    // public array $types = [];
    // #[LiveProp(writable: true, url: new UrlMapping(as: 's'))]
    // public array $specs = [];

    public function __construct(private ProductRepository $productRepository, private LoggerInterface $logger)
    {
    }

    #[LiveListener('receiveQuery')]
    public function receiveQuery(#[LiveArg] string $query)
    {
        $this->query = $query;
        $this->sendCategoriesForTree();
    }

    #[LiveListener('receiveCategories')]
    public function receiveCategories(#[LiveArg] array $newCategories)
    {
        if (array_key_exists('included', $newCategories)) {
            $this->includedCategories = $newCategories['included'];
        }
        if (array_key_exists('excluded', $newCategories)) {
            $this->excludedCategories = $newCategories['excluded'];
        }
        if (!$newCategories) {
            $this->excludedCategories = [];
            $this->includedCategories = [];
        }

        $this->sendCategoriesForTree();
    }

    #[LiveAction]
    public function setFilter(
        #[LiveArg] array $newFilters = [],
    ) {
        $this->logger->info('settingFilter');
        $this->logger->info(print_r($newFilters, true));
        $filterKey = $newFilters['key'];
        $newValue = $newFilters['value'];
        if (!array_key_exists($filterKey, $this->filters)) {
            $this->filters[$filterKey][] = $newValue;
        } elseif ($filterKey === 'specs') {
            $newSpecKey = $newFilters['keySpecs'];
            if (!array_key_exists($newSpecKey, $this->filters['specs'])) {
                $this->filters['specs'][$newSpecKey][] = $newValue;
            } else {
                $collection = new ArrayCollection($this->filters['specs'][$newSpecKey]);
                $collection->exists(fn($key, $value) => $value === $newValue)
                    ? $collection->removeElement($newValue)
                    : $collection->add($newValue);
                $result = $collection->toArray();
                if (!$result) {
                    unset($this->filters['specs'][$newSpecKey]);
                } else {
                    $this->filters['specs'][$newSpecKey] = $result;
                }
            }
        } else {
            $collection = new ArrayCollection($this->filters[$filterKey]);
            $collection->exists(fn($key, $value) => $value === $newValue)
                ? $collection->removeElement($newValue)
                : $collection->add($newValue);
            $result = $collection->toArray();
            if (!$result) {
                unset($this->filters[$filterKey]);
            } else {
                $this->filters[$filterKey] = $result;
            }
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

        $categoriesResult = $this->productRepository->getCategoriesFromSearch($this->query, $this->includedCategories, $this->excludedCategories, $this->filters);
        if ($this->includedCategories || $this->excludedCategories) {
            $this->logger->info('this is active:');
            $this->logger->info(print_r($categoriesResult, true));
            $categories['active'] = $categoriesResult;
            $this->logger->info('this is chosen:');
            $this->logger->info(print_r($this->includedCategories, true));
            $categories['chosen'] = $this->includedCategories;
            $this->logger->info('this is exluded:');
            $this->logger->info(print_r($this->excludedCategories, true));
            $categories['excluded'] = $this->excludedCategories;
        } else {
            $categories['neutral'] = $categoriesResult;
        }
        $this->emit('redraw', [
            'newCatalogs' => $categories,
        ]);
    }


    public function getProducts()
    {
        // example method that returns an array of Products
        return $this->productRepository->getPaginatedValues($this->query, $this->includedCategories, $this->excludedCategories, $this->filters, $this->page);
    }
}
