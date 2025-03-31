<?php

namespace App\Entity;

class Money
{
    private const RATES = [
        'CZK' => 1,
        'USD' => 0.043,
        'EUR' => 0.040,
    ];

    private int $figure;
    private string $currency;

    public function __construct(int $figure, ?string $currency = 'CZK')
    {
        $this->figure = $figure;
        $this->currency = $currency;
    }

    public function getFigure(): ?int
    {
        return $this->figure;
    }

    public function getCurrency(): ?int
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
