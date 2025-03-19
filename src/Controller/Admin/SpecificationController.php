<?php

namespace App\Controller\Admin;

use App\Entity\Specification;
use App\Form\SpecificationType;
use App\Repository\SpecificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('admin/specification')]
final class SpecificationController extends AbstractController
{
    #[Route(name: 'specification.index', methods: ['GET'])]
    public function index(SpecificationRepository $specificationRepository): Response
    {
        return $this->render('admin/specification/index.html.twig', [
            'specifications' => $specificationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'specification.new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $specification = new Specification();
        $form = $this->createForm(SpecificationType::class, $specification);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($specification);
            $entityManager->flush();

            return $this->redirectToRoute('specification.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/specification/new.html.twig', [
            'specification' => $specification,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'specification.show', methods: ['GET'])]
    public function show(Specification $specification): Response
    {
        return $this->render('admin/specification/show.html.twig', [
            'specification' => $specification,
        ]);
    }

    #[Route('/{id}/edit', name: 'specification.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Specification $specification, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SpecificationType::class, $specification);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('specification.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/specification/edit.html.twig', [
            'specification' => $specification,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'specification.delete', methods: ['POST'])]
    public function delete(Request $request, Specification $specification, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $specification->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($specification);
            $entityManager->flush();
        }

        return $this->redirectToRoute('specification.index', [], Response::HTTP_SEE_OTHER);
    }
}
