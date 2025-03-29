<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;

#[Route('admin/product')]
final class ProductController extends AbstractController
{
    #[Route(name: 'admin.product.index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('admin/product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin.product.new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ImageUploader $imageUploader,
    ): Response {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->getData()->getImages()) {
                foreach ($form->get('images') as $imageData) {
                    $image = $imageData->getData();
                    $imageFile = $imageData->get('uploadImages')->getData();
                    $fullFilePath = $imageUploader->upload($imageFile, $product);

                    $image->setPath($fullFilePath);
                    $entityManager->persist($image);
                    $product->addImage($image);
                }
                // $imageFile = $form->get('images')[0]->get('uploadImages')->getData();
            }
            $stripe = new StripeClient($this->getParameter('app.stripeKey'));
            $productStripe = $stripe->products->create([
                'name' => $product->getName(),
                'description' => '$12/Month subscription',
            ]);
            $price = $stripe->prices->create([
                'unit_amount' => $product->getPrice(),
                'currency' => 'usd',
                'product' => $productStripe['id'],
            ]);
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('admin.product.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin.product.show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('admin/product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin.product.edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Product $product,
        EntityManagerInterface $entityManager,
        ImageUploader $imageUploader,
    ): Response {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->getData()->getImages()) {
                foreach ($form->get('images') as $imageData) {
                    $image = $imageData->getData();
                    $imageFile = $imageData->get('uploadImages')->getData();
                    $fullFilePath = $imageUploader->upload($imageFile, $product);

                    $image->setPath($fullFilePath);
                    $entityManager->persist($image);
                    $product->addImage($image);
                }
                // $imageFile = $form->get('images')[0]->get('uploadImages')->getData();
            }

            $entityManager->flush();

            return $this->redirectToRoute('admin.product.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin.product.delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin.product.index', [], Response::HTTP_SEE_OTHER);
    }
}
