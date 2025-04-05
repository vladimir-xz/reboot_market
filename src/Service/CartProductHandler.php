<?php

namespace App\Service;

use App\Dto\CartDto;
use App\Entity\Product;
use App\Entity\Money;

class CartProductHandler
{
    public static function add(CartDto $cart, Product $product): ?CartDto
    {
        $amountAlreadyInCart = $cart->getAmountOfProduct($product->getId());
        $currentAmountInCart = $product->getAmountInCart();
        if ($product->hasNotEnoughInStockOrNegative($amountAlreadyInCart + $currentAmountInCart)) {
            return null;
        }

        $product->setAmountInCart($amountAlreadyInCart + $currentAmountInCart);
        $productsPrice = $product->getPrice() * $currentAmountInCart;
        $productsWeight = $product->getWeight() * $currentAmountInCart;

        $newCost = $cart->getTotalPrice() + $productsPrice;
        $cart->setTotalWeight($cart->getTotalWeight() + $productsWeight);
        $cart->setTotalPrice($newCost);
        $cart->addProduct($product);

        return $cart;
    }
}
