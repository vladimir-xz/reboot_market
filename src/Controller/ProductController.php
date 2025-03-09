<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/products/{id}', name: 'product.index', methods: ['GET', 'HEAD'])]
    public function index(int $id, ProductRepository $repository): Response
    {

        $product = $repository->findOneByIdJoinedToSpecificationsAndImages($id);

        if (!$product) {
            throw $this->createNotFoundException('The product does not exist');
            // the above is just a shortcut for:
            // throw new NotFoundHttpException('The product does not exist');
        }

        return $this->render('products/index.html.twig', [
            'product' => $product,
            'treeMap' => [],
        ]);
    }
}
