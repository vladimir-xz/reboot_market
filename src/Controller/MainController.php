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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'homepage', methods: ['GET', 'HEAD'])]
    public function homepage(
        CatalogBuilder $builder,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
    ): Response {


        $rawArr = $categoryRepository->getRawTree();
        $catalog = $builder->build($rawArr);
        $products = $productRepository->findAll();

        return $this->render('homepage.html.twig', [
            'products' => $products,
            'catalog' => $catalog,
        ]);
    }
}
