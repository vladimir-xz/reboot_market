<?php

namespace App\Twig\Components;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Cart
{
    use DefaultActionTrait;

    public ?\stdClass $cart;
    public int $total = 0;

    public function __construct(RequestStack $requestStack)
    {
        $this->cart = json_decode($requestStack->getCurrentRequest()->cookies->get('cart', ''));
        $this->total = $this->cart->total ?? 0;
    }

    public function getProducts()
    {
        return (array) $this->cart?->ids ?? 'Cart is emty';
    }
}
