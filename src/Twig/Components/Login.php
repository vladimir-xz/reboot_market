<?php

namespace App\Twig\Components;

use App\Entity\User;
use App\Form\LoginType;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Bundle\SecurityBundle\Security;

#[AsLiveComponent]
final class Login extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    /**
     * The initial data used to create the form.
     */
    #[LiveProp]
    public ?User $initialFormData = null;

    public function __construct(
        private AuthenticationUtils $authenticationUtils,
        private LoggerInterface $log,
        private Security $security,
        private UserRepository $userRepository
    ) {
    }

    #[LiveAction]
    public function login(Request $request)
    {
        $this->submitForm();

        $submittedToken = $request->getPayload()->get('token');

        // 'delete-item' is the same value used in the template to generate the token
        if ($this->isCsrfTokenValid('user_login', $submittedToken)) {
            $this->log->info('not valid csrf');
        }

        $email = $this->getForm()->getData()->getEmail();
        $user = $this->userRepository->findOneBy(['email' => $email]);
        $this->security->login($user);

        return $this->redirectToRoute($request->headers->get('referer'));
    }

    protected function instantiateForm(): FormInterface
    {
        // we can extend AbstractController to get the normal shortcuts
        return $this->createForm(LoginType::class, $this->initialFormData);
    }


    // public function getLastUsername()
    // {
    //     // get the login error if there is one
    //     // $error = $authenticationUtils->getLastAuthenticationError();

    //     // last username entered by the user
    //     return $this->authenticationUtils->getLastUsername();
    // }

    // public function getError()
    // {
    //     return $this->authenticationUtils->getLastAuthenticationError();
    // }
}
