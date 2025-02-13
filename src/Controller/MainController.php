<?php

namespace App\Controller;

use App\Service\CatalogBuilder;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\MainCategory;
use App\Repository\MainCategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'homepage', methods: ['GET', 'HEAD'])]
    public function homepage(
        Request $request,
        CatalogBuilder $builder,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
    ): Response {

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

        $allCatFromProd = $productRepository->getCategoriesFromSearch($query, $activeCategories);
        $allCatFromCat = $categoryRepository->getCategoriesFromSearch($query, $activeCategories);

        // $products = $productRepository->getPaginatedValues($query, $activeCategories, $page);
        // $productsNotPad = $productRepository->findByNameField($query, $activeCategories);
        // $categories = $productRepository->getCategoriesFromSearch($query, $activeCategories);

        return $this->render('homepage.html.twig', [
            // 'notPaginated' => $productsNotPad,
            'query' => $query,
            'catsProd' => $allCatFromProd,
            'catsCat' => $allCatFromCat,
            'categories' => $categories ?? [],
            'page' => $page,
            'brands' => $brands
            // 'array' => $array,
        ]);
    }
}
