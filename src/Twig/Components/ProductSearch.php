<?php

namespace App\Twig\Components;

use App\Repository\ProductRepository;
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
    #[LiveProp(writable: true, url: new UrlMapping(as: 'b'))]
    public array $brands = [];
    #[LiveProp(writable: true, url: new UrlMapping(as: 't'))]
    public array $types = [];
    #[LiveProp(writable: true, url: new UrlMapping(as: 's'))]
    public array $specs = [];

    public function __construct(private ProductRepository $productRepository, private LoggerInterface $logger)
    {
    }

    #[LiveListener('search')]
    public function search(#[LiveArg] string $query = '', #[LiveArg] array $newCatalogs = [])
    {
        if (!$query && !$newCatalogs) {
            $this->emit('redraw', [
                'newCatalogs' => [],
            ]);
        }
        if ($query) {
            $this->query = $query;
        }
        if ($newCatalogs) {
            $this->categories = $newCatalogs;
        }

        $this->categories = $newCatalogs;
        $categories = $this->productRepository->getCategoriesFromSearch($query, $newCatalogs);
        // $this->dispatchBrowserEvent('product:search', [
        //     'activeCategories' => $categories,
        // ]);
        $this->getProducts();
        $this->emit('redraw', [
            'newCatalogs' => $categories,
        ]);
    }


    public function getProducts()
    {
        // example method that returns an array of Products
        return $this->productRepository->getPaginatedValues($this->query, $this->categories, $this->page);
    }
}
