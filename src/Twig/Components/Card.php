<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use App\Entity\Product;

#[AsTwigComponent]
final class Card
{
    public Product $product;
}
