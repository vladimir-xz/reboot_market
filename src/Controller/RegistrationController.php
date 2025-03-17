<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Address;
use App\Form\UserType;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class RegistrationController extends AbstractController
{
    #[Route('/registration', name: 'registraiton')]
    public function index(Request $request, LoggerInterface $log): Response
    {
        $validator = Validation::createValidator();
        $user = new User();

        // dummy code - add some example tags to the task
        // (otherwise, the template will render an empty list of tags)
        $address = new Address();
        $user->addAddress($address);

        // end dummy code

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        // $input = ['password' => $form["password"]->getData(), 'repeatPassword' => $form["repeatPassword"]->getData()];

        // $constraint = new Assert\Collection([
        //     'password' => new Assert\Length(['min' => 8]),
        //     'repeatPassword' => new Assert\Callback(['callback' => function ($value, ExecutionContextInterface $ec) {
        //         if ($ec->getRoot()['password'] !== $value) {
        //             $ec->addViolation('Passwords do not match');
        //         }
        //     }]),
        // ]);

        // $violations = $validator->validate($input, $constraint);

        if ($form->isSubmitted() && $form->isValid()) {
            // ... do your form processing, like saving the Task and Tag entities
            $log->info(print_r($form->getData(), true));
            return $this->redirectToRoute('homepage');
        }

        return $this->render('registration/index.html.twig', [
            'form' => $form,
        ]);
    }
}
