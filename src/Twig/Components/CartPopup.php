<?php

namespace App\Twig\Components;

use App\Dto\CartDto;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class CartPopup extends AbstractController
{
    use DefaultActionTrait;

    public ?CartDto $cart;

    public function __construct(private RequestStack $requestStack, private LoggerInterface $logger)
    {
    }

    public function mount()
    {
        $this->cart = $this->requestStack->getCurrentRequest()->getSession()->get('cart', new CartDto());
    }

    public function getProducts()
    {
        return $this->cart->getProducts()?->getValues() ?? 'Cart is emty';
    }

    public function getTotal()
    {
        if ($this->cart === null) {
            return 0;
        }

        return $this->cart->getTotalPrice();
    }
}
