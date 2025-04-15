<?php

namespace App\Dto;

use App\Entity\Money;
use App\Entity\Product;
use Doctrine\Common\Collections\ArrayCollection;

class CartDto
{
    private ?int $totalWeight;
    private ?int $totalPrice;
    private ArrayCollection $products;

    public function __construct(
        int $totalWeight = 0,
        int $totalPrice = 0,
        array $products = []
    ) {
        $this->products = new ArrayCollection($products);
        $this->totalPrice = $totalPrice;
        $this->totalWeight = $totalWeight;
    }

    public function getTotalWeight()
    {
        return $this->totalWeight;
    }

    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function setProducts(ArrayCollection $products)
    {
        $this->products = $products;
    }

    public function setTotalWeight(int $weight)
    {
        $this->totalWeight = $weight;
    }

    public function setTotalPrice(int $price)
    {
        $this->totalPrice = $price;
    }
}
