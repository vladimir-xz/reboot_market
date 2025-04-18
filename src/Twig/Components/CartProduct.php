<?php

namespace App\Twig\Components;

use App\Dto\ProductCartDto;
use App\Service\CartProductHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class CartProduct
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    #[LiveProp(writable: ['quantity'], updateFromParent: true)]
    public ProductCartDto $product;

    public function __construct(private LoggerInterface $log, private SerializerInterface $serializer, ProductCartDto $product)
    {
        $this->product = $product;
    }

    #[LiveAction]
    public function increment()
    {
        $quantity = $this->product->getQuantity();
        $this->product->setQuantity($quantity + 1);
        $this->emitUp('increment', [
            'product' => $this->product->getId()
        ]);
    }

    #[LiveAction]
    public function decrement()
    {
        $quantity = $this->product->getQuantity();
        $this->product->setQuantity($quantity - 1);
        $this->emitUp('decrement', [
            'product' => $this->product->getId()
        ]);
    }

    #[LiveAction]
    public function setAmount()
    {
        $this->emitUp('changeAmount', [
            'productId' => $this->product->getId(),
            'amount' => $this->product->getQuantity()
        ]);
    }

    #[LiveAction]
    public function delete()
    {
        $this->emitUp('delete', [
            'product' => $this->product->getId()
        ]);
    }
}
