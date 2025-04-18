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
            throw new \Exception('Not enough amount in stock');
        }

        $productInCart = $cart
            ->getProducts()
            ->findFirst(fn(int $key, ProductCartDto $value) => $value->getId() === $product->getId());
        if ($productInCart === null) {
            $productInCart = new ProductCartDto($product, $quantity);
            $cart->getProducts()->add($productInCart);
        } else {
            $productInCart->setQuantity($productInCart->getQuantity() + $quantity);
        }

        return $this->calculate($cart, $productInCart, fn($total, $new) => $total + $new, $quantity);
    }

    public function increment(CartDto $cart, int $productId)
    {
        $productInCart = $cart
            ->getProducts()
            ->findFirst(fn(int $key, ProductCartDto $value) => $value->getId() === $productId);
        if ($productInCart === null) {
            throw new \Exception('Product is not in a cart');
        }
        $newAmount = $productInCart->getQuantity() + 1;
        if ($productInCart->getAvalible() < $newAmount) {
            throw new \Exception('Not enough amount in stock');
        }

        $productInCart->setQuantity($newAmount);

        return $this->calculate($cart, $productInCart, fn($total, $new) => $total + $new);
    }

    public function decrement(CartDto $cart, int $productId)
    {
        $productInCart = $cart
            ->getProducts()
            ->findFirst(fn(int $key, ProductCartDto $value) => $value->getId() === $productId);
        if ($productInCart === null) {
            throw new \Exception('Product is not in a cart');
        }

        $newAmount = $productInCart->getQuantity() - 1;
        if ($newAmount < 1) {
            throw new \Exception('Value is less then one');
        }

        $productInCart->setQuantity($newAmount);

        return $this->calculate($cart, $productInCart, fn($total, $new) => $total - $new);
    }

    public function delete(CartDto $cart, int $productId)
    {
        $products = $cart->getProducts();
        $productInCart = $products
            ->findFirst(fn(int $key, ProductCartDto $value) => $value->getId() === $productId);
        if ($productInCart === null) {
            throw new \Exception('Product is not in a cart');
        }

        $products->removeElement($productInCart);
        $this->calculate($cart, $productInCart, fn($total, $new) => $total - $new, $productInCart->getQuantity());
    }

    public function changeAmount(CartDto $cart, int $productId, int $newAmount, $log)
    {
        $productInCart = $cart
            ->getProducts()
            ->findFirst(fn(int $key, ProductCartDto $value) => $value->getId() === $productId);

        if ($productInCart === null) {
            throw new \Exception('Product is not in a cart');
        }

        if ($productInCart->getAvalible() < $newAmount || $newAmount < 1) {
            throw new \Exception('Not enough in stock or less then one');
        }

        $difference = $productInCart->getQuantity() - $newAmount;
        $productInCart->setQuantity($newAmount);

        if ($difference < 0) {
            $log->info('doing plus with number ' . abs($difference) . ', so the total amount is ' . $productInCart->getPrice() * $difference);
            return $this->calculate($cart, $productInCart, fn($total, $new) => $total + $new, abs($difference));
        } else {
            $log->info('doing minus with number ' . $difference . ', so the total amount should be ' . $productInCart->getPrice() * $difference);
            return $this->calculate($cart, $productInCart, fn($total, $new) => $total - $new, $difference);
        }
    }

    private function calculate(CartDto $cart, ProductCartDto $product, \Closure $action, int $amount = 1)
    {
        $productsPrice = $product->getPrice() * $amount;
        $productsWeight = $product->getWeight() * $amount;
        $newCost = $action($cart->getTotalPrice(), $productsPrice);
        $newWeight = $action($cart->getTotalWeight(), $productsWeight);
        $cart->setTotalPrice($newCost);
        $cart->setTotalWeight($newWeight);

        return $cart;
    }
}
