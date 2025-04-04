<?php

namespace App\Entity;

class Money
{
    private const RATES = [
        'czk' => 1,
        'usd' => 0.043,
        'eur' => 0.040,
    ];

    private float $figure;
    private string $currency;

    public function __construct(int $figure, ?string $currency = 'czk')
    {
        $this->figure = $figure;
        $this->currency = $currency;
    }

    public function getFigure(): ?float
    {
        return $this->figure;
    }

    public function getPriceToDisplay(): ?float
    {
        return $this->figure / 100;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        if (array_key_exists($currency, $this::RATES)) {
            $this->currency = $currency;
            $this->figure *= $this::RATES[$currency];
        }

        return $this;
    }
}
