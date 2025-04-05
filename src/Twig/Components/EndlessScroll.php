<?php

namespace App\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use App\Repository\ProductRepository;

#[AsLiveComponent]
final class EndlessScroll
{
    use DefaultActionTrait;

    #[LiveProp]
    public int $page;
    public string $query = '';
    public array $includedCategories = [];
    public array $filters = [];
    public array $excludedCategories = [];

    public function __construct(private ProductRepository $productRepository)
    {
    }

    public function getProducts()
    {
        return $this->productRepository->getPaginatedValues($this->query, $this->includedCategories, $this->excludedCategories, $this->filters, $this->page);
    }
}
