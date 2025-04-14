<?php

namespace App\Twig\Components;

use App\Entity\Money;
use App\Dto\CartDto;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Cart
{
    use DefaultActionTrait;

    #[LiveProp]
    public ?CartDto $cart = null;
    #[LiveProp(writable: ['amountInCart'])]
    /** @var Product[] */
    public $products = [];

    public function __construct(private RequestStack $requestStack, private LoggerInterface $log)
    {
    }

    #[LiveAction]
    public function increment(#[LiveArg] int $id)
    {
        $products = $this->cart->getIdsAndProducts();
        $product = $products[$id];
        if ($product->getAmountInCart() >= $product->amount) {
            return;
        }

        $product->setAmountInCart($product->getAmountInCart() + 1);
        $products[$id] = $product;

        $this->requestStack->getCurrentRequest()->getSession()->set('cart', $this->cart);
    }

    #[LiveAction]
    public function decrement(#[LiveArg] int $id)
    {
        $products = $this->cart->getIdsAndProducts();
        $product = $products[$id];
        if ($product->getAmountInCart() <= 1) {
            return;
        }

        $product->setAmountInCart($product->getAmountInCart() - 1);
        $products[$id] = $product;

        $this->requestStack->getCurrentRequest()->getSession()->set('cart', $this->cart);
    }

    #[LiveAction]
    public function setAmount(#[LiveArg] int $id)
    {
        $products = $this->cart->getIdsAndProducts();
        $product = $products[$id];
        // if ($this->amount > $product->getAmount() || $this->amount < 1) {
        //     return;
        // }

        // $product->setAmountInCart($this->amount);
        $products[$id] = $product;

        $this->requestStack->getCurrentRequest()->getSession()->set('cart', $this->cart);
    }

    #[LiveAction]
    public function deleteProduct(#[LiveArg] int $id, #[LiveArg] int $amount)
    {
        $products = $this->cart->getIdsAndProducts();
        unset($products[$id]);

        $this->requestStack->getCurrentRequest()->getSession()->set('cart', $this->cart);
    }

    #[LiveListener('productAdded')]
    public function setNewCartDto()
    {
    }

    public function getProducts()
    {
        if ($this->cart === null) {
            $this->cart = $this->requestStack->getCurrentRequest()->getSession()->get('cart', new CartDto());
            $this->products = $this->cart->getIdsAndProducts();
        }
        return $this->cart?->getIdsAndProducts() ?? 'Cart is emty';
    }

    public function getTotal()
    {
        if ($this->cart === null) {
            return 0;
        }

        return $this->cart->getTotalPrice();
    }
}
