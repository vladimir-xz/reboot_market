<?php

namespace App\Dto;

use App\Entity\Country;
use App\Entity\ShippingMethod;

class FreightDataDto
{
    private string $postcode;
    private int $weight;
    private Country $country;
    private ShippingMethod $shippingMethod;

    public function __construct(string $postcode, int $weight, Country $country, ShippingMethod $shippingMethod)
    {
        $this->postcode = $postcode;
        $this->weight = $weight;
        $this->country = $country;
        $this->shippingMethod = $shippingMethod;
    }

    public function getPostcode()
    {
        return $this->postcode;
    }

    public function getWeight()
    {
        return $this->weight;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function getShippingMethod()
    {
        return $this->shippingMethod;
    }
}
