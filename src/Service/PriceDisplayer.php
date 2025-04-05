<?php

namespace App\Service;

use App\Entity\Money;
use Symfony\Component\HttpFoundation\RequestStack;

class PriceDisplayer
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    public function adjustPrice(Money $money)
    {
        $currency = $this->getActual();
        return $money->setCurrency($currency)->getFigure() / 100;
    }

    public function getActual()
    {
        return $this->requestStack->getCurrentRequest()->getSession()->get('currency', 'czk');
    }
}
