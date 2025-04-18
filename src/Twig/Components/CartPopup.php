<?php

namespace App\Twig\Components;

use App\Dto\CartDto;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

#[AsLiveComponent]
final class CartPopup extends AbstractController
{
    use DefaultActionTrait;

    public ?CartDto $cart;
    #[LiveProp]
    public bool $wasShown = false;

    public function __construct(private RequestStack $requestStack, private LoggerInterface $logger)
    {
    }

    public function mount()
    {
        $this->cart = $this->requestStack->getCurrentRequest()->getSession()->get('cart', new CartDto());
        $this->wasShown = true;
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
