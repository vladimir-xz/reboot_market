<?php

namespace App\Dto;

use App\Entity\Address;
use App\Entity\Country;
use App\Entity\ShippingMethod;

class PaymentDataDto
{
    private ?string $postcode;
    private ?int $weight;
    private ?int $countryId;
    private ?int $shippingMethodId;
    private array $products;

    public function __construct(
        ?string $postcode = null,
        ?int $countryId = null,
        ?int $weight = null,
        ?int $method = null,
        array $idsAndAmounts = [],
    ) {
        $this->postcode = $postcode;
        $this->countryId = $countryId;
        $this->weight = $weight;
        $this->shippingMethodId = $method;
        $this->products = $idsAndAmounts;
    }

    public function getPostcode()
    {
        return $this->postcode;
    }

    public function getWeight()
    {
        return $this->weight;
    }

    public function getCountryId()
    {
        return $this->countryId;
    }

    public function getShippingMethodId()
    {
        return $this->shippingMethodId;
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function setPostcode(string $postcode)
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function setWeight(int $weight)
    {
        $this->weight = $weight;

        return $this;
    }

    public function setCountryId(int $countryId)
    {
        $this->countryId = $countryId;

        return $this;
    }

    public function setShippingMethodId(int $shippingMethodId)
    {
        $this->shippingMethodId = $shippingMethodId;

        return $this;
    }

    public function setProducts(array $idsAndAmounts)
    {
        $this->products = $idsAndAmounts;

        return $this;
    }
}
