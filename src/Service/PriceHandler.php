<?php

namespace App\Service;

use App\Entity\Money;
use Symfony\Component\HttpFoundation\RequestStack;

class PriceHandler
{
    private const RATES = [
        'czk' => 1,
        'usd' => 0.043,
        'eur' => 0.040,
    ];

    public function __construct(private RequestStack $requestStack)
    {
    }

    public function display(int $price)
    {
        $currency = $this->getActual();
        return $this->convertToCurrency($price, $currency) / 100;
    }

    public function getActual()
    {
        return $this->requestStack->getCurrentRequest()->getSession()->get('currency', 'czk');
    }

    public function convertToCurrency(int $figure, string $currency)
    {
        return $figure * $this::RATES[$currency];
    }
}
