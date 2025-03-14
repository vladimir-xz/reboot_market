<?php

namespace App\Twig\Components;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class CartPopup extends AbstractController
{
    use DefaultActionTrait;

    public ?string $cart;

    public function __construct(RequestStack $requestStack, private LoggerInterface $logger)
    {
        $this->cart = $requestStack->getCurrentRequest()->cookies->get('cart');
    }

    public function getProducts()
    {
        return $this->cart ?? 'Cart is emty';
    }
}
