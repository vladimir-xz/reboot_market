<?php

namespace App\Service;

use App\Dto\CartDto;
use App\Entity\Product;
use App\Entity\Money;
use Closure;
use Psr\Log\LoggerInterface;

class CartProductHandler
{
    public static function add(CartDto $cart, Product $product, LoggerInterface $log): ?CartDto
    {
        $currentAmountInCart = $product->getAmountInCart();
        $products = $cart->getProducts();
        $productInCart = $products->findFirst(fn(int $key, Product $value) => $value === $product);
        if ($productInCart === null) {
            $products->add($product);
        } elseif (
            $product->hasNotEnoughInStockOrNegative($productInCart->getAmountInCart() + $currentAmountInCart)
        ) {
            return $cart;
        } else {
            $productInCart->setAmountInCart($productInCart->getAmountInCart() + $currentAmountInCart);
        }

        $productsPrice = $product->getPrice() * $currentAmountInCart;
        $productsWeight = $product->getWeight() * $currentAmountInCart;

        $log->info(print_r($productInCart, true));
        $log->info(print_r($products, true));
        $newCost = $cart->getTotalPrice() + $productsPrice;
        $cart->setTotalWeight($cart->getTotalWeight() + $productsWeight);
        $cart->setTotalPrice($newCost);
        $cart->setProducts($products);

        return $cart;
    }

    public static function increment(CartDto $cart, int $id)
    {
        $products = $cart->getProducts();
        $productInCart = $products->findFirst(fn(int $key, Product $value) => $value->getId() === $id);
        if ($productInCart === null) {
            return $cart;
        } elseif (
            $productInCart->hasNotEnoughInStockOrNegative($productInCart->getAmountInCart() + 1)
        ) {
            return $cart;
        } else {
            $productInCart->setAmountInCart($productInCart->getAmountInCart() + 1);
        }

        $newCost = $cart->getTotalPrice() + $productInCart->getPrice();
        $cart->setTotalWeight($cart->getTotalWeight() + $productInCart->getWeight());
        $cart->setTotalPrice($newCost);
        $cart->setProducts($products);

        return $cart;
    }

    public static function decrement(CartDto $cart, int $id)
    {
        $products = $cart->getProducts();
        $productInCart = $products->findFirst(fn(int $key, Product $value) => $value->getId() === $id);
        if ($productInCart === null) {
            return $cart;
        } elseif (
            $productInCart->hasNotEnoughInStockOrNegative($productInCart->getAmountInCart() - 1)
        ) {
            return $cart;
        } else {
            $productInCart->setAmountInCart($productInCart->getAmountInCart() - 1);
        }

        $newCost = $cart->getTotalPrice() - $productInCart->getPrice();
        $cart->setTotalWeight($cart->getTotalWeight() - $productInCart->getWeight());
        $cart->setTotalPrice($newCost);
        $cart->setProducts($products);

        return $cart;
    }

    private static function calculate(CartDto $cart, int $id, Closure $action)
    {
        $products = $cart->getProducts();
        $productInCart = $products->findFirst(fn(int $key, Product $value) => $value->getId() === $id);
        if ($productInCart === null) {
            return $cart;
        }

        $newAmount = $action($productInCart->getAmountInCart(), 1);
        if ($productInCart->hasNotEnoughInStockOrNegative($newAmount)) {
            return $cart;
        } else {
            $productInCart->setAmountInCart($newAmount);
        }

        $newCost = $action($cart->getTotalPrice(), $productInCart->getPrice());
        $cart->setTotalWeight($action($cart->getTotalWeight(), $productInCart->getWeight()));
        $cart->setTotalPrice($newCost);
        $cart->setProducts($products);

        return $cart;
    }
}
