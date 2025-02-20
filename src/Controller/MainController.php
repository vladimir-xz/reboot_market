<?php

namespace App\Controller;

use App\Service\CatalogBuilder;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\MainCategory;
use App\Repository\MainCategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Service\MapAllRecords;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Psr\Log\LoggerInterface;

class MainController extends AbstractController
{
    #[Route('/', name: 'homepage', methods: ['GET', 'HEAD'])]
    public function homepage(
        Request $request,
        CatalogBuilder $builder,
        ProductRepository $productRepository,
        MapAllRecords $mapAllRecords,
        CategoryRepository $categoryRepository,
        LoggerInterface $logger
    ): Response {

        $logger->info('normal load query');
        $logger->info(print_r($request->query->all(), true));
        $allParams = $request->query->all();
        $page = $allParams['page'] ?? 1;
        $query = $allParams['q'] ?? '';
        $activeCategories = $allParams['c'] ?? [];
        $brands = $allParams['b'] ?? [];
        if (is_string($activeCategories)) {
            $activeCategories = [];
        }
        if (is_string($brands)) {
            $brands = [];
        }
        $brands = json_encode($brands);

        $allRecords = $productRepository->getAllProductsWithCategoryAndFilters($query, $activeCategories, [], ['brand' => ['Dell' => 'Dell'], 'type' => ['server' => 'server']]);

        $rawArr = $categoryRepository->getRawTree();
        $result = $builder->build($rawArr);

        $parents = $result['parents'];
        $lastChildren = $result['lastChildren'];
        // $products = $productRepository->getPaginatedValues($query, $activeCategories, $page);
        // $productsNotPad = $productRepository->findByNameField($query, $activeCategories);
        // $categories = $productRepository->getCategoriesFromSearch($query, $activeCategories);

        return $this->render('homepage.html.twig', [
            // 'notPaginated' => $productsNotPad,
            'all' => $allRecords,
            'categories' => [],
            'parents' => $parents,
            'lastChildren' => $lastChildren,
            'query' => $query,
            'page' => $page,
            'brands' => $brands
            // 'array' => $array,
        ]);
    }
}
