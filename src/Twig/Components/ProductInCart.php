<?php

namespace App\Twig\Components;

use App\Dto\ProductCartDto;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class ProductInCart
{
    public ProductCartDto $product;

    public function getTotalSum()
    {
        return $this->product->getPrice() * $this->product->getQuantity();
    }
}
