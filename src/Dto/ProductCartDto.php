<?php

namespace App\Dto;

use App\Entity\Product;

class ProductCartDto
{
    private ?int $id;
    private ?string $name;
    private ?int $avalible;
    private ?int $amountInCart;
    private ?int $quantity;
    private ?string $image;
    private ?int $price;

    public function __construct(Product $product = new Product())
    {
        $this->id = $product->getId();
        $this->name = $product->getName();
        $this->avalible = $product->getAmount();
        $this->amountInCart = $product->getAmountInCart();
        $this->image = $product->getMainImagePath();
        $this->price = $product->getPrice();
    }

    // Getter and setter for $id
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    // Getter and setter for $name
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    // Getter and setter for $avalible
    public function getAvalible(): ?int
    {
        return $this->avalible;
    }

    public function setAvalible(?int $avalible): void
    {
        $this->avalible = $avalible;
    }

    // Getter and setter for $amountInCart
    public function getAmountInCart(): ?int
    {
        return $this->amountInCart;
    }

    public function setAmountInCart(?int $amountInCart): void
    {
        $this->amountInCart = $amountInCart;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): void
    {
        $this->quantity = $quantity;
    }

    // Getter and setter for $image
    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    // Getter and setter for $price
    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): void
    {
        $this->price = $price;
    }
}
