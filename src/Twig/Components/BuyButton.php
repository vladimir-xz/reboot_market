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

    #[LiveProp(writable: ['amountInCart'])]
    public Product $product;

    public function __construct(private RequestStack $requestStack, private LoggerInterface $log)
    {
    }

    #[LiveAction]
    public function save(#[LiveArg] int $amount)
    {
        $newAmount = $this->product->getAmountInCart() + $amount;
        if ($this->product->hasNotEnoughInStockOrNegative($newAmount)) {
            return;
        }

        $this->product->setAmountInCart($newAmount);
        $session = $this->requestStack->getCurrentRequest()->getSession();
        $cart = $session->get('cart', new CartDto());
        if (
            $this->product->hasNotEnoughInStockOrNegative(
                $cart->getAmountOfProduct($this->product->getId()) + $amount
            )
        ) {
            return;
        }
        $cart->addProduct($this->product);
        $session->set('cart', $cart);

        $this->emit('productAdded');
    }
}
