<?php

namespace App\Twig\Components;

use App\Entity\Product;
use App\Dto\CartDto;
use App\Service\CartProductHandler;
use Doctrine\Common\Collections\ArrayCollection;
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
    public ?CartDto $cart;
    // #[LiveProp(writable: ['amountInCart'])]
    // /** @var Product[] */
    // public $products;

    public function __construct(
        private RequestStack $requestStack,
        private CartProductHandler $cartHandler,
        private LoggerInterface $log
    ) {
        $cart = $requestStack->getCurrentRequest()->getSession()->get('cart', new CartDto());
        $this->cart = $cart;
        // $this->totalPrice = $cart->getTotalPrice();
        // $this->totalWeight = $cart->getTotalWeight();
        // $filteredCollection = $cart->getProducts()->filter(function ($element) {
        //     return $element !== null;
        // });
        // $this->products = $filteredCollection->getValues();
    }

    #[LiveAction]
    public function increment(#[LiveArg] int $id)
    {
        // $newCart = new CartDto($this->totalWeight, $this->totalPrice, $this->products);
        $cart = $this->cartHandler->increment($this->cart, $id);

        $this->requestStack->getCurrentRequest()->getSession()->set('cart', $cart);
    }

    #[LiveAction]
    public function decrement(#[LiveArg] int $id)
    {
        // $newCart = new CartDto($this->totalWeight, $this->totalPrice, $this->products);
        $cart = $this->cartHandler->decrement($this->cart, $id);

        $this->requestStack->getCurrentRequest()->getSession()->set('cart', $cart);
    }

    #[LiveAction]
    public function setAmount(#[LiveArg] int $id)
    {
        // $product = $this->products->findFirst(fn(int $key, Product $value) => $value->getId() === $id);
        // $newCart = new CartDto($this->totalWeight, $this->totalPrice, $this->products);
        // $cart = $this->cartHandler->add($this->cart, $product, $this->log);

        // $this->requestStack->getCurrentRequest()->getSession()->set('cart', $cart);
    }

    #[LiveAction]
    public function deleteProduct(#[LiveArg] int $id, #[LiveArg] int $amount)
    {
        // $products = $this->cart->getIdsAndProducts();
        // unset($products[$id]);

        // $this->requestStack->getCurrentRequest()->getSession()->set('cart', $this->cart);
    }

    #[LiveListener('productAdded')]
    public function setNewCartDto()
    {
        $cart = $this->requestStack->getCurrentRequest()->getSession()->get('cart', new CartDto());

        $this->cart = $cart;
        // $this->totalPrice = $cart->getTotalPrice();
        // $this->totalWeight = $cart->getTotalWeight();
        // $this->products = $cart->getProducts();
    }

    #[LiveAction]
    public function getProducts()
    {
        return $this->products ?? 'Cart is emty';
    }

    public function getTotal()
    {
        if ($this->cart === null) {
            return 0;
        }

        return $this->cart->getTotalPrice();
    }
}
