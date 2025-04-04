<?php

namespace App\Dto;

use App\Entity\Product;

class CartDto
{
    private ?int $totalWeight;
    private ?int $totalPrice;
    private array $idsAndProducts;

    public function __construct(int $totalWeight = 0, int $totalPrice = 0, array $idsAndProducts = [])
    {
        $this->idsAndProducts = $idsAndProducts;
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

    public function getIdsAndProducts()
    {
        return $this->idsAndProducts;
    }

    public function getAmountOfProduct(int $id): int
    {
        if (!array_key_exists($id, $this->idsAndProducts)) {
            return 0;
        }
        return $this->idsAndProducts[$id]->getAmountInCart();
    }

    public function addProduct(Product $product, int $amount)
    {
        $id = $product->getId();
        $this->idsAndProducts[$id] = $product;
        $this->totalPrice += $product->getPrice() * $amount;
        $this->totalWeight += $product->getWeight() * $amount;
    }
}
