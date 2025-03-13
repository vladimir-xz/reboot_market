<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class BuyButton
{
    public int $max;
    public int $id;
    public int $price;
    public string $name;
}
