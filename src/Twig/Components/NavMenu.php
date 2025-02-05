<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class NavMenu
{
    public array $categories;
    public int $offset = 6;
}
