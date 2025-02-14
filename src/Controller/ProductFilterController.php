<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
    public function getAllFilters(string $brands): Response
    {
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

            $accumulator['brand'][$company] = $company;
            $accumulator['type'][$type] = $type;
            if ($currentMax < $price) {
                $accumulator['price']['max'] = $price;
            } elseif (!isset($accumulator['Price']['min'])) {
                $accumulator['price']['min'] = $price;
            } elseif ($accumulator['Price']['min'] > $price) {
                $accumulator['price']['min'] = $price;
            }

            foreach ($specs as $spec) {
                $property = $spec->getProperty();
                $propValue = $spec->getValue();
                $accumulator[$property][$propValue] = $propValue;
            }

            return $accumulator;
        }, []);

        $this->logger->info(print_r($filter, true));

        return $this->render('static/_filter.html.twig', [
            'filter' => $filter,
            'brands' => $brands
        ]);
    }
}
