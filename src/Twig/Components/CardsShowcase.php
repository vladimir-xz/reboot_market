<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Pagerfanta\Pagerfanta;

#[AsTwigComponent]
final class CardsShowcase
{
    public Pagerfanta $cards;
}
