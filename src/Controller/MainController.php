<?php

namespace App\Controller;

use App\Service\CatalogBuilder;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\MainCategory;
use App\Repository\MainCategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Service\CatalogHandler;
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
        CatalogHandler $builder,
        ProductRepository $productRepository,
        LoggerInterface $logger
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

        $recentlyAdded = $productRepository->getRecentlyAdded();

        // $products = $productRepository->getPaginatedValues($query, $activeCategories, $page);
        // $productsNotPad = $productRepository->findByNameField($query, $activeCategories);
        // $categories = $productRepository->getCategoriesFromSearch($query, $activeCategories);

        return $this->render('homepage.html.twig', [
            'treeMap' => [],
            'query' => $query,
            'page' => $page,
            'brands' => $brands,
            'recents' => $recentlyAdded,
            // 'array' => $array,
        ]);
    }
}
