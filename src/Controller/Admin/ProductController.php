<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
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
            // $imageFile = $form->get('image')->getData();
            $images = $form->getData()->getImages();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($images) {
                for ($i = 0; $i < count($images); $i++) {
                    $image = $images[$i];
                    $imageFile = $form->get('images')[$i]->get('uploadImages')->getData();
                    $fullFilePath = $imageUploader->upload($imageFile, $product);

                    $image->setPath($fullFilePath);
                    $entityManager->persist($image);
                    $product->addImage($image);
                }
                // $imageFile = $form->get('images')[0]->get('uploadImages')->getData();
            }
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
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
