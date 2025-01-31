<?php

namespace App\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\LiveListener;

#[AsLiveComponent]
class RandomNumber
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public int $max = 1000;

    #[LiveListener('productAdded')]
    public function getRandomNumber(): int
    {
        return rand(0, 1000);
    }
}
