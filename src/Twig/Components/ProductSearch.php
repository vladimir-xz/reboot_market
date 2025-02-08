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
use Pagerfanta\Pagerfanta;
use Psr\Log\LoggerInterface;

#[AsLiveComponent]
class ProductSearch extends AbstractController
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    #[LiveProp(writable: true)]
    public int $page = 1;
    public string $query = '';
    public array $categories = [];

    /** @var Product[] */
    public $products = [];

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

        $this->logger->info('searching');
        $this->categories = $newCatalogs;
        $page = 1;
        $this->products = $this->productRepository->getPaginatedValues($query, $this->categories, $page);
        $categories = $this->productRepository->getCategoriesFromSearch($query, $newCatalogs);
        // $this->dispatchBrowserEvent('product:search', [
        //     'activeCategories' => $categories,
        // ]);
        $this->emit('redraw', [
            'newCatalogs' => $categories,
        ]);
    }

    #[LiveAction]
    public function getProducts(#[LiveArg('page')] int $page)
    {
        // example method that returns an array of Products
        $this->products = $this->productRepository->getPaginatedValues($this->query, $this->categories, $page);
        $this->logger->info($this->page);
    }
}
