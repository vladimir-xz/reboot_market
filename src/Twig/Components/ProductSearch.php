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

    /** @var Product[] */
    public $products = [];

    public function __construct(private ProductRepository $productRepository)
    {
    }

    #[LiveListener('search')]
    public function getSearch(#[LiveArg] string $query = '')
    {
        $this->products = $this->productRepository->findByNameField($query);
        $this->dispatchBrowserEvent('product:search', [
            'product' => ['a' => 1, 'b' => 2, 'c' => 3],
        ]);
    }

    public function getProducts(): array
    {
        // example method that returns an array of Products
        return $this->products;
    }
}
