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
    #[LiveProp]
    /** @var ProductCartDto[] */
    public $products;

    public function __construct(
        private RequestStack $requestStack,
        private CartProductHandler $cartHandler,
        private DenormalizerInterface&NormalizerInterface $serializer,
        private LoggerInterface $log
    ) {
    }

    public function mount(): void
    {
        $cart = $this->requestStack->getCurrentRequest()->getSession()->get('cart', new CartDto());
        $this->cart = $cart;
        $this->products = $this->cart->getProducts()->toArray();
    }

    #[LiveAction]
    public function increment(#[LiveArg] int $id)
    {
        // $newCart = new CartDto($this->totalWeight, $this->totalPrice, $this->products);
        $this->log->info('this is increment');
        $this->log->info(print_r($this->cart->getProducts(), true));
        $cart = $this->cartHandler->increment($this->cart, $id);

        $this->requestStack->getCurrentRequest()->getSession()->set('cart', $cart);
    }

    #[LiveAction]
    public function decrement(#[LiveArg] int $id)
    {
        // $newCart = new CartDto($this->totalWeight, $this->totalPrice, $this->products);
        $cart = $this->cartHandler->decrement($this->cart, $id, $this->log);

        $this->cart = $cart;
        $this->requestStack->getCurrentRequest()->getSession()->set('cart', $cart);
    }

    #[LiveAction]
    public function setAmount(#[LiveArg] int $id)
    {
        $this->log->info('setting amount happening');
        $this->log->info(print_r($this->products, true));
    }

    #[LiveAction]
    public function delete(#[LiveArg] int $id)
    {
        $cart = $this->cartHandler->delete($this->cart, $id, $this->log);
        $cart = $this->cartHandler->delete($this->cart, $id, $this->log);

        $this->cart = $cart;
        $this->requestStack->getCurrentRequest()->getSession()->set('cart', $cart);
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
        $products = $this->serializer->denormalize($data['products'], ProductCartDto::class . '[]',);
        return new CartDto($data['totalWeight'], $data['totalPrice'], $products);
    }
}
