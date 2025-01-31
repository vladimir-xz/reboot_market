<?php

namespace App\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ExampleLive
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $inputValue = 'helo';

    public function __construct()
    {
    }

    #[LiveAction]
    public function getProducts(#[LiveArg] string $inputValue = 'helo'): string
    {
        // example method that returns an array of Products
        $this->inputValue = $inputValue;
        return $this->inputValue;
    }
}
