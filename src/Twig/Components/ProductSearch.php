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

#[AsLiveComponent]
class ProductSearch extends AbstractController
{
    use DefaultActionTrait;
    use ComponentToolsTrait;


    #[LiveProp(writable: true)]
    public string $currentProducts = '';

    public string $query = '';
    public array $newCatalogs = [];
    public array $categories = [];

    /** @var Product[] */
    public $products = [];

    public function __construct(private ProductRepository $productRepository)
    {
    }

    #[LiveListener('search')]
    public function getSearch(#[LiveArg] string $query = '', #[LiveArg] array $newCatalogs = [])
    {
        if ($query) {
            $this->query = $query;
        }
        if ($newCatalogs) {
            $this->newCatalogs = $newCatalogs;
        }

        $this->products = $this->productRepository->findByNameField($this->query, $this->newCatalogs);
        $categories = $this->productRepository->getCategoriesFromSearch($this->query, $this->newCatalogs);
        // $this->dispatchBrowserEvent('product:search', [
        //     'activeCategories' => $categories,
        // ]);
        $this->emit('redraw', [
            'newCatalogs' => $categories,
        ]);
    }

    public function getProducts(): array
    {
        // example method that returns an array of Products
        return $this->products;
    }
}
