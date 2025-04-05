<?php

namespace App\Twig\Components;

use App\Entity\Money;
use App\Dto\CartDto;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Cart
{
    use DefaultActionTrait;

    public CartDto $cart;
    public string $currency;

    public function __construct(private RequestStack $requestStack, private LoggerInterface $log)
    {
        $this->cart = $this->requestStack->getCurrentRequest()->getSession()->get('cart', new CartDto());
        $this->currency = $this->requestStack->getCurrentRequest()->getSession()->get('currency', 'czk');
    }

    #[LiveListener('productAdded')]
    public function setNewCartDto()
    {
    }

    public function getProducts()
    {
        return $this->cart?->getIdsAndProducts() ?? 'Cart is emty';
    }

    public function getTotal()
    {
        if ($this->cart === null) {
            return null;
        }
        return $this->cart?->getTotalPrice();
    }
}
