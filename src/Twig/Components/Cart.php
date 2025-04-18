<?php

namespace App\Twig\Components;

use App\Entity\Product;
use App\Dto\CartDto;
use App\Dto\ProductCartDto;
use App\Service\CartProductHandler;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
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

    #[LiveProp(hydrateWith: 'hydrateCart', dehydrateWith: 'dehydrateCart')]
    public ?CartDto $cart;

    public function __construct(
        private RequestStack $requestStack,
        private CartProductHandler $cartHandler,
        private DenormalizerInterface&NormalizerInterface $serializer,
        private LoggerInterface $log
    ) {
    }

    public function mount(): void
    {
        $this->cart = $this->requestStack->getCurrentRequest()->getSession()->get('cart', new CartDto());
    }

    #[LiveListener('increment')]
    public function increment(#[LiveArg] int $product)
    {
        try {
            $cart = $this->cartHandler->increment($this->cart, $product);
            $this->cart = $cart;
            $this->requestStack->getCurrentRequest()->getSession()->set('cart', $cart);
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
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    #[LiveListener('delete')]
    public function delete(#[LiveArg] int $product)
    {
        try {
            $cart = $this->cartHandler->delete($this->cart, $product);
            $this->cart = $cart;
            $this->requestStack->getCurrentRequest()->getSession()->set('cart', $cart);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    #[LiveListener('productAdded')]
    public function setNewCartDto()
    {
        $cart = $this->requestStack->getCurrentRequest()->getSession()->get('cart', new CartDto());

        $this->cart = $cart;
    }

    #[LiveAction]
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

    public function dehydrateCart(?CartDto $cart)
    {
        $products = $cart->getProducts()->toArray();
        return [
            'totalWeight' => $cart->getTotalWeight(),
            'totalPrice' => $cart->getTotalPrice(),
            'products' => $this->serializer->normalize($products),
        ];
    }

    public function hydrateCart($data)
    {
        $products = $this->serializer->denormalize($data['products'], ProductCartDto::class . '[]');
        return new CartDto($data['totalWeight'], $data['totalPrice'], $products);
    }
}
