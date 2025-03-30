<?php

namespace App\Dto;

use App\Entity\Address;
use App\Entity\Country;
use App\Entity\ShippingMethod;

class PaymentDataDto
{
    private array $address;
    private array $country;
    private array $shippingMethod;
    private array $products;
    private ?int $weight;

    public function __construct(
        ?Address $address = null,
        ?Country $country = null,
        ?int $weight = null,
        ?ShippingMethod $shippingMethod = null,
        array $idsAndAmounts = [],
    ) {
        $this->address = [
            'postcode' => $address?->getPostcode(),
            'firstLine' => $address?->getFirstLine(),
            'secondLine' => $address?->getSecondLine(),
            'town' => $address?->getTown(),
        ];
        $this->country = [
            'id' => $country?->getId(),
            'name' => $country?->getName(),
        ];
        $this->weight = $weight;
        $this->shippingMethod = [
            'id' => $shippingMethod?->getId(),
            'name' => $shippingMethod?->getName()
        ];
        $this->products = $idsAndAmounts;
    }

    public function getAddress()
    {
        return $this->address;
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

    public function getWeight()
    {
        return $this->weight;
    }

    public function setAddress(array $address)
    {
        $this->address = $address;

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

    public function setWeight(int $weight)
    {
        $this->weight = $weight;

        return $this;
    }
}
