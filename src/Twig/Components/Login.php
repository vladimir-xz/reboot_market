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
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;

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
        private UserRepository $userRepository,
    ) {
    }

    #[LiveAction]
    public function login(Request $request)
    {
        $this->submitForm();

        $submittedToken = $request->getPayload()->get('token');
        if ($this->isCsrfTokenValid('user_login', $submittedToken)) {
            throw new \Exception('Not valid csrf token');
        }

        $user = $this->getForm()->getData();
        $this->security->login(
            $user,
            authenticatorName: 'form_login',
            badges: $this->getForm()->get('remember_me')->getData() ? [(new RememberMeBadge())->enable()] : []
        );

        return $this->redirect($request->headers->get('referer'));
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
