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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;

final class RegistrationController extends AbstractController
{
    #[Route('/registration', name: 'registraiton')]
    public function index(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        Security $security,
        LoggerInterface $log
    ): Response {
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
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );

            $user->setPassword($hashedPassword);
            $entityManager->persist($user);
            $entityManager->flush();

            $security->login(
                $user,
                authenticatorName: 'form_login',
                badges: [(new RememberMeBadge())->enable()]
            );

            return $this->redirectToRoute('homepage');
        }

        return $this->render('registration/index.html.twig', [
            'form' => $form,
        ]);
    }
}
