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
    public array $categories = [];
    #[LiveProp(writable: true, url: new UrlMapping(as: 'f'))]
    public array $filters = [];
    // #[LiveProp(writable: true, url: new UrlMapping(as: 't'))]
    // public array $types = [];
    // #[LiveProp(writable: true, url: new UrlMapping(as: 's'))]
    // public array $specs = [];

    public function __construct(private ProductRepository $productRepository, private LoggerInterface $logger)
    {
    }

    #[LiveListener('search')]
    public function search(
        #[LiveArg] string $query = '',
        #[LiveArg] array $newCatalogs = [],
        #[LiveArg] array $newFilters = [],
    ) {
        if (!$query && !$newCatalogs && !$newFilters) {
            $this->emit('redraw', [
                'newCatalogs' => [],
            ]);
            return;
        }

        if ($query) {
            $this->query = $query;
        }
        if ($newCatalogs) {
            $this->categories = $newCatalogs;
        }
        if ($newFilters) {
            $filterKey = $newFilters['key'];
            $newValue = $newFilters['value'];
            if (!array_key_exists($filterKey, $this->filters)) {
                $this->logger->info('adding new filter');
                $this->filters[$filterKey][] = $newValue;
                $this->logger->info(print_r($this->filters, true));
            } elseif ($filterKey === 'specs') {
                $newSpecKey = $newFilters['keySpecs'];
                if (!array_key_exists($newSpecKey, $this->filters['specs'])) {
                    $this->filters['specs'][$newSpecKey][] = $newValue;
                } else {
                    $collection = new ArrayCollection($this->filters['specs'][$newSpecKey]);
                    $collection->exists(fn($key, $value) => $value === $newValue)
                        ? $collection->removeElement($newValue)
                        : $collection->add($newValue);
                    $this->filters['specs'][$newSpecKey] = $collection->toArray();
                }
            } else {
                $this->logger->info('appending new filter');
                $collection = new ArrayCollection($this->filters[$filterKey]);
                $this->logger->info(print_r($collection, true));
                $collection->exists(fn($key, $value) => $value === $newValue)
                    ? $collection->removeElement($newValue)
                    : $collection->add($newValue);

                $this->filters[$filterKey] = $collection->toArray();
            }
        }
        $this->logger->info(print_r($this->filters, true));

        $categories = $this->productRepository->getCategoriesFromSearch($query, $newCatalogs);
        // $this->dispatchBrowserEvent('product:search', [
        //     'activeCategories' => $categories,
        // ]);
        // $this->getProducts();
        $this->emit('redraw', [
            'newCatalogs' => $categories,
        ]);
    }


    public function getProducts()
    {
        // example method that returns an array of Products
        return $this->productRepository->getPaginatedValues($this->query, $this->categories, $this->page, $this->filters);
    }
}
