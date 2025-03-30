<?php

namespace App\Dto;

use App\Entity\Address;
use App\Entity\Country;
use App\Entity\ShippingMethod;

class PaymentDataDto
{
    private ?string $postcode;
    private ?int $weight;
    private ?array $country;
    private ?array $shippingMethod;
    private array $products;

    public function __construct(
        ?string $postcode = null,
        ?int $countryId = null,
        ?string $countryName = null,
        ?int $weight = null,
        ?int $methodId = null,
        ?string $methodName = null,
        array $idsAndAmounts = [],
    ) {
        $this->postcode = $postcode;
        $this->country = [
            'id' => $countryId,
            'name' => $countryName,
        ];
        $this->weight = $weight;
        $this->shippingMethod = [
            'id' => $methodId,
            'name' => $methodName
        ];
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

    public function getCountry()
    {
        return $this->country;
    }

    public function getShippingMethod()
    {
        return $this->shippingMethod;
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

    public function setCountry(array $country)
    {
        $this->country = $country;

        return $this;
    }

    public function setShippingMethod(array $shippingMethod)
    {
        $this->shippingMethod = $shippingMethod;

        return $this;
    }

    public function setProducts(array $idsAndAmounts)
    {
        $this->products = $idsAndAmounts;

        return $this;
    }
}
