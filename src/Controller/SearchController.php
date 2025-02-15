<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;
use Psr\Log\LoggerInterface;

final class SearchController extends AbstractController
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    #[Route('/_search', name: 'searchStream')]
    public function getProductSearch(Request $request): Response
    {
        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
        return $this->renderBlock('search/index.html.twig', 'search_block');
    }

    #[Route('/search', name: 'search')]
    public function index(Request $request): Response
    {
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
        // $products = $productRepository->getPaginatedValues($query, $activeCategories, $page);
        // $productsNotPad = $productRepository->findByNameField($query, $activeCategories);
        // $categories = $productRepository->getCategoriesFromSearch($query, $activeCategories);

        return $this->render('homepage.html.twig', [
            // 'notPaginated' => $productsNotPad,
            'categories' => $categories ?? [],
            'page' => $page,
            'brands' => $brands
            // 'array' => $array,
        ]);
    }
}
