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
        $productsPrice = new Money($product->getPrice() * $currentAmountInCart);
        $productsWeight = $product->getWeight() * $currentAmountInCart;

        $priceInCart = $cart->getTotalPrice();
        $priceInCart->addFigure($productsPrice);
        $cart->setTotalWeight($cart->getTotalWeight() + $productsWeight);
        $cart->setTotalPrice($priceInCart);
        $cart->addProduct($product);

        return $cart;
    }
}
