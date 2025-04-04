<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\Cache;
use App\Repository\ProductRepository;
use App\Service\MapAllRecords;
use Psr\Log\LoggerInterface;

final class ProductFilterController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private MapAllRecords $mapAllRecords,
        private LoggerInterface $logger
    ) {
    }

    // #[Cache(smaxage: 6000)]
    public function getAllFilters(Request $request): Response
    {
        $allParams = $request->query->all();
        $brands = $allParams['b'] ?? [];
        $currency = $request->getSession()->get('currency', 'czk');

        if (is_string($brands)) {
            $brands = [];
        }
        $brands = json_encode($brands);

        $allProducts = $this->productRepository->getAllWithSpecs();

        // $filter = array_reduce(function (array $accumulator, Product $value): array {
        // }, []);
        $filter = $this->mapAllRecords->mapRecords($allProducts, $currency);

        // $this->logger->info(print_r($filter, true));

        return $this->render('static/_filter.html.twig', [
            'filter' => $filter,
            'brands' => $brands
        ]);
    }
}
