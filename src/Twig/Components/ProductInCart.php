<?php

namespace App\Twig\Components;

use App\Entity\Money;
use App\Entity\Product;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class ProductInCart
{
    public Product $product;

    public function getTotalSum()
    {
        return new Money($this->product->getPrice() * $this->product->getAmountInCart());
    }
}
