<?php

namespace App\Twig\Components;

use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Login
{
    use DefaultActionTrait;

    public function __construct(private AuthenticationUtils $authenticationUtils)
    {
    }


    public function getLastUsername()
    {
        // get the login error if there is one
        // $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        return $this->authenticationUtils->getLastUsername();
    }

    public function getErrors()
    {
        return $this->authenticationUtils->getLastAuthenticationError();
    }
}
