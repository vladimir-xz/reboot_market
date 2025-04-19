<?php

namespace App\Twig\Components\Cart;

use App\Dto\CartDto;
use App\Service\CartProductHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\ComponentToolsTrait;

#[AsLiveComponent]
final class CheckoutList
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    #[LiveProp]
    public ?CartDto $cart;

    public function __construct(
        private RequestStack $requestStack,
        private CartProductHandler $cartHandler,
        private LoggerInterface $log
    ) {
    }

    #[LiveListener('increment')]
    public function increment(#[LiveArg] int $product)
    {
        try {
            $cart = $this->cartHandler->increment($this->cart, $product);
            $this->cart = $cart;
            $this->requestStack->getCurrentRequest()->getSession()->set('cart', $cart);

            $this->emit('CartChanged');
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    #[LiveListener('decrement')]
    public function decrement(#[LiveArg] int $product)
    {
        try {
            $cart = $this->cartHandler->decrement($this->cart, $product);
            $this->cart = $cart;
            $this->requestStack->getCurrentRequest()->getSession()->set('cart', $cart);

            $this->emit('CartChanged');
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    #[LiveListener('changeAmount')]
    public function changeAmount(#[LiveArg] int $productId, #[LiveArg] int $amount)
    {
        try {
            $cart = $this->cartHandler->changeAmount($this->cart, $productId, $amount, $this->log);
            $this->cart = $cart;
            $this->requestStack->getCurrentRequest()->getSession()->set('cart', $cart);

            $this->emit('CartChanged');
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    #[LiveListener('delete')]
    public function delete(#[LiveArg] int $product)
    {
        try {
            $cart = $this->cartHandler->delete($this->cart, $product, $this->log);
            $this->cart = $cart;
            $this->requestStack->getCurrentRequest()->getSession()->set('cart', $cart);

            $this->emit('CartChanged');
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function getProducts()
    {
        return $this->cart?->getProducts()?->getValues() ?? 'Cart is emty';
    }
}
