<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Address;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RegistrationController extends AbstractController
{
    #[Route('/registration', name: 'registraiton')]
    public function index(Request $request): Response
    {
        $user = new User();

        // dummy code - add some example tags to the task
        // (otherwise, the template will render an empty list of tags)
        $address = new Address();
        $user->addAddress($address);

        // end dummy code

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ... do your form processing, like saving the Task and Tag entities
        }

        return $this->render('registration/index.html.twig', [
            'form' => $form,
        ]);
    }
}
