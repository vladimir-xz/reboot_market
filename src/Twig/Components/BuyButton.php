<?php

namespace App\Twig\Components;

use App\Entity\Product;
use App\Dto\CartDto;
use Psr\Log\LoggerInterface;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class BuyButton
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    #[LiveProp]
    public Product $product;

    public function __construct(private RequestStack $requestStack, private LoggerInterface $log)
    {
    }

    #[LiveAction]
    public function save(#[LiveArg] int $amount)
    {
        $this->log->info(print_r($this->product->getMainImagePath(), true));
        $session = $this->requestStack->getCurrentRequest()->getSession();
        $cart = $session->get('cart', new CartDto());
        $amountInCart = $cart->getAmountOfProduct($this->product->getId());
        if ($this->product->hasNotEnoughInStockOrNegative($amountInCart + $amount)) {
            return;
        }

        $this->product->setAmountInCart($amountInCart + $amount);
        $cart->addProduct($this->product, $amount);
        $session->set('cart', $cart);

        $this->emit('productAdded');
    }
}
