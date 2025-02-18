<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\Cache;
use App\Repository\ProductRepository;
use Psr\Log\LoggerInterface;

final class ProductFilterController extends AbstractController
{
    public function __construct(private ProductRepository $productRepository, private LoggerInterface $logger)
    {
    }

    // #[Cache(smaxage: 6000)]
    public function getAllFilters(Request $request): Response
    {
        $allParams = $request->query->all();
        $brands = $allParams['b'] ?? [];

        if (is_string($brands)) {
            $brands = [];
        }
        $brands = json_encode($brands);

        $this->logger->info('Computing filter template');
        $allProducts = $this->productRepository->getAllWithSpecs();

        // $filter = array_reduce(function (array $accumulator, Product $value): array {
        // }, []);
        $filter = array_reduce($allProducts, function ($accumulator, $value) {
            $company = $value->getBrand();
            $price = $value->getPrice();
            $type = $value->getType();
            $specs = $value->getSpecifications();
            $currentMax = $accumulator['price']['max'] ?? 0;
            $currentMin = $accumulator['price']['min'] ?? 0;

            $accumulator['brand'][$company] = $company;
            $accumulator['type'][$type] = $type;
            if ($currentMax < $price && $currentMin === 0) {
                $accumulator['price']['max'] = $price;
                $accumulator['price']['min'] = $currentMax;
            } elseif ($currentMax < $price) {
                $accumulator['price']['max'] = $price;
            } elseif ($currentMin === 0 || $currentMin > $price) {
                $accumulator['price']['min'] = $price;
            }
            $this->logger->info($price);
            $this->logger->info(print_r($accumulator['price']));

            foreach ($specs as $spec) {
                $property = $spec->getProperty();
                $propValue = $spec->getValue();
                $accumulator[$property][$propValue] = $propValue;
            }

            return $accumulator;
        }, []);

        // $this->logger->info(print_r($filter, true));

        return $this->render('static/_filter.html.twig', [
            'filter' => $filter,
            'brands' => $brands
        ]);
    }
}
