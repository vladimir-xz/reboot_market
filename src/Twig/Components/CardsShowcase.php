<?php

namespace App\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use App\Repository\ProductRepository;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Psr\Log\LoggerInterface;
use Pagerfanta\Pagerfanta;

#[AsLiveComponent]
final class CardsShowcase
{
    use DefaultActionTrait;

    public Pagerfanta $cards;

    public function __construct(ProductRepository $productRepository)
    {
        $this->cards = $productRepository->getRecentlyAdded();
    }
}
