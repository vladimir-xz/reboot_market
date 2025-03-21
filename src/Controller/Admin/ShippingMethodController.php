<?php

namespace App\Controller\Admin;

use App\Entity\ShippingMethod;
use App\Form\ShippingMethodType;
use App\Repository\ShippingMethodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('admin/shipping/method')]
final class ShippingMethodController extends AbstractController
{
    #[Route(name: 'shipping.index', methods: ['GET'])]
    public function index(ShippingMethodRepository $shippingMethodRepository): Response
    {
        return $this->render('admin/shipping/index.html.twig', [
            'shippings' => $shippingMethodRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'shipping.new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $shippingMethod = new ShippingMethod();
        $form = $this->createForm(ShippingMethodType::class, $shippingMethod);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($shippingMethod);
            $entityManager->flush();

            return $this->redirectToRoute('shipping.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/shipping/new.html.twig', [
            'shipping' => $shippingMethod,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'shipping.show', methods: ['GET'])]
    public function show(ShippingMethod $shippingMethod): Response
    {
        return $this->render('admin/shipping/show.html.twig', [
            'shipping' => $shippingMethod,
        ]);
    }

    #[Route('/{id}/edit', name: 'shipping.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ShippingMethod $shippingMethod, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ShippingMethodType::class, $shippingMethod);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('shipping.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/shipping/edit.html.twig', [
            'shipping' => $shippingMethod,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'shipping.delete', methods: ['POST'])]
    public function delete(Request $request, ShippingMethod $shippingMethod, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $shippingMethod->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($shippingMethod);
            $entityManager->flush();
        }

        return $this->redirectToRoute('shipping.index', [], Response::HTTP_SEE_OTHER);
    }
}
