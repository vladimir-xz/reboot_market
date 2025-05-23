<?php

namespace App\Twig\Components\Cart;

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
final class Product
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    #[LiveProp(writable: ['quantity'])]
    public ProductCartDto $product;

    #[LiveAction]
    public function increment()
    {
        $quantity = $this->product->getQuantity();
        if ($quantity + 1 > $this->product->getAvalible()) {
            return;
        }

        $this->product->setQuantity($quantity + 1);
        $this->emitUp('increment', [
            'product' => $this->product->getId()
        ]);
    }

    #[LiveAction]
    public function decrement()
    {
        $quantity = $this->product->getQuantity();
        if ($quantity - 1 < 1) {
            return;
        }

        $this->product->setQuantity($quantity - 1);
        $this->emitUp('decrement', [
            'product' => $this->product->getId()
        ]);
    }

    #[LiveAction]
    public function setAmount()
    {
        $quantity = $this->product->getQuantity();
        if ($quantity < 1) {
            $this->product->setQuantity(1);
            $this->emitUp('changeAmount', [
                'productId' => $this->product->getId(),
                'amount' => $this->product->getQuantity()
            ]);
        } elseif ($quantity > $this->product->getAvalible()) {
            $this->product->setQuantity($this->product->getAvalible());
            $this->emitUp('changeAmount', [
                'productId' => $this->product->getId(),
                'amount' => $this->product->getQuantity()
            ]);
        } else {
            $this->emitUp('changeAmount', [
                'productId' => $this->product->getId(),
                'amount' => $this->product->getQuantity()
            ]);
        }
    }

    #[LiveAction]
    public function delete()
    {
        $this->emitUp('delete', [
            'product' => $this->product->getId()
        ]);
    }
}
