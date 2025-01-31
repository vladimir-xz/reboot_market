<?php

namespace App\Twig\Components;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class ProductSearch extends AbstractController
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $querySearch = '';

    public function __construct()
    {
    }

    #[LiveListener('search')]
    public function getSearch(#[LiveArg] string $product)
    {
        $this->querySearch = $product;
    }

    public function getProducts(): string
    {
        // example method that returns an array of Products
        return $this->querySearch;
    }
}
