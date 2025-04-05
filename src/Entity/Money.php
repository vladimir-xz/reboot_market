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

    public function __construct(int|Money $figure = 0, ?string $currency = 'czk')
    {
        if ($figure instanceof Money) {
            $czk = $figure->getFigure() / $this::RATES[$figure->getCurrency()];
            $this->figure = $czk * $this::RATES[$currency];
            $this->currency = $currency;
        } else {
            $this->figure = $figure;
            $this->currency = $currency;
        }
    }

    public function getFigure(): ?float
    {
        return $this->figure;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        if (array_key_exists($currency, $this::RATES)) {
            $czk = $this->figure / $this::RATES[$this->currency];
            $this->figure = $czk * $this::RATES[$currency];
            $this->currency = $currency;
        }

        return $this;
    }

    public function displayPrice(): ?float
    {
        return $this->figure / 100;
    }

    public function addFigure(Money $money)
    {
        if ($money->getCurrency() !== $this->getCurrency()) {
            $sameCurrency = new Money($money, $this->currency);
            $this->figure += $sameCurrency->getFigure();
        } else {
            $this->figure += $money->getFigure();
        }

        return $this;
    }
}
