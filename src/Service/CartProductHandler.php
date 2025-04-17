<?php

namespace App\Service;

use App\Dto\CartDto;
use App\Dto\ProductCartDto;
use App\Entity\Product;
use Psr\Log\LoggerInterface;

class CartProductHandler
{
    public function __construct(private LoggerInterface $log)
    {
    }

    public function add(CartDto $cart, Product $product, int $quantity, LoggerInterface $log): ?CartDto
    {
        if ($product->getAmount() < $quantity) {
            return $cart;
        }

        $productInCart = $cart
            ->getProducts()
            ->findFirst(fn(int $key, ProductCartDto $value) => $value->getId() === $product->getId());
        if ($productInCart === null) {
            $productInCart = new ProductCartDto($product);
            $cart->getProducts()->add($productInCart);
        } else {
            $productInCart->setQuantity($productInCart->getQuantity() + $quantity);
        }

        return $this->calculate($cart, $productInCart, fn($x, $y) => $x + $y, $quantity);
    }

    public function increment(CartDto $cart, int $id)
    {
        $products = $cart->getProducts();
        $productInCart = $products->findFirst(fn(int $key, ProductCartDto $value) => $value->getId() === $id);
        if ($productInCart === null) {
            return $cart;
        }
        $newAmount = $productInCart->getQuantity() + 1;
        if ($productInCart->getAvalible() < $newAmount) {
            return $cart;
        }

        $productInCart->setQuantity($newAmount);

        return $this->calculate($cart, $productInCart, fn($x, $y) => $x + $y);
    }

    public function decrement(CartDto $cart, int $id, LoggerInterface $log)
    {
        $products = $cart->getProducts();
        $productInCart = $products->findFirst(fn(int $key, ProductCartDto $value) => $value->getId() === $id);
        if ($productInCart === null) {
            return $cart;
        }
        $newAmount = $productInCart->getQuantity() - 1;
        if ($newAmount < 1) {
            return $cart;
        }

        $productInCart->setQuantity($newAmount);

        return $this->calculate($cart, $productInCart, fn($x, $y) => $x - $y);
    }

    public function delete(CartDto $cart, int $id, LoggerInterface $log)
    {
        $products = $cart->getProducts();
        $productInCart = $products->findFirst(fn(int $key, ProductCartDto $value) => $value->getId() === $id);
        if ($productInCart === null) {
            return $cart;
        }

        $products->removeElement($productInCart);
        $this->calculate($cart, $productInCart, fn($x, $y) => $x - $y, $productInCart->getQuantity());
    }

    public function changeAmount(CartDto $cart, int $id, $quantity)
    {
        $productInCart = $cart
            ->getProducts()
            ->findFirst(fn(int $key, ProductCartDto $value) => $value->getId() === $id);

        if ($productInCart === null) {
            return $cart;
        }

        if ($productInCart->getAvalible() < $quantity || $quantity < 1) {
            return $cart;
        }

        $productInCart->setQuantity($quantity);

        return $this->calculate($cart, $productInCart, fn($x, $y) => $x + $y, $quantity);
    }

    private function calculate(CartDto $cart, ProductCartDto $product, \Closure $action, int $amount = 1)
    {
        $productsPrice = $product->getPrice() * $amount;
        $productsWeight = $product->getWeight() * $amount;
        $newCost = $action($cart->getTotalPrice(), $productsPrice);
        $cart->setTotalWeight($action($cart->getTotalWeight(), $productsWeight));
        $cart->setTotalPrice($newCost);

        return $cart;
    }
}
