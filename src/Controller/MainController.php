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
use Symfony\Component\Translation\LocaleSwitcher;

class MainController extends AbstractController
{
    public function __construct(
        private LocaleSwitcher $localeSwitcher,
    ) {
    }

    #[Route('/', methods: ['GET', 'HEAD'])]
    public function indexNoLocale(Request $request): Response
    {
        return $this->redirectToRoute('homepage');
    }

    #[Route('/{_locale}/', name: 'homepage', methods: ['GET', 'HEAD'])]
    public function homepage(
        Request $request,
        CatalogHandler $builder,
        ProductRepository $productRepository,
        LoggerInterface $logger
    ): Response {

        $recentlyAdded = $productRepository->getRecentlyAdded();

        return $this->render('homepage.html.twig', [
            'recents' => $recentlyAdded,
        ]);
    }
}
