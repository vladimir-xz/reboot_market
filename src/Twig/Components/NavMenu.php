<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class NavMenu
{
    public array $categories;
    public array $leftPaddings = [
        'pl-6', 'pl-9', 'pl-12', 'pl-15', 'pl-18'
    ];
    public int $offset = 0;
}
