<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Product;
use App\Repository\CountryRepository;
use App\Repository\FreightRateRepository;
use App\Repository\FreightRepository;
use App\Repository\ProductRepository;
use App\Repository\ShippingMethodRepository;
use App\Service\FreightCostGetter;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    #[Route('/cart', name: 'cart')]
    public function index(
        Request $request,
        ProductRepository $productRep,
        Security $security,
        FreightCostGetter $freightCostGetter,
        FreightRateRepository $freightRateRepository,
        CountryRepository $countryRepository,
        LoggerInterface $log,
    ): Response {
        $cart = json_decode($request->cookies->get('cart', '{}'), true);
        if (!$cart) {
            return $this->render('cart/index.html.twig', [
                'products' => [],
                'productsTotal' => null,
                'treeMap' => [],
                'address' => null,
                'allMethods' => null,
                'currentMethod' => null,
                'totalWeight' => null,
                'freightCost' => null,
                'totalPrice' => null,
            ]);
        }

        $ids = array_keys($cart['ids'] ?? []);
        $products = new ArrayCollection($productRep->findSomeByIds($ids));
        $result = $products->reduce(function (array $acc, Product $product) use ($cart): array {
            if (!isset($acc['products'])) {
                $acc['products'] = [];
                $acc['totalPrice'] = 0;
                $acc['totalWeight'] = 0;
                $acc['errors'] = [];
            }

            $amountInCart = $cart['ids'][$product->getId()]['amount'] ?? 1;

            if ($product->hasEnoughInStockAndNotNegative($amountInCart)) {
                $product->setAmountInCart($amountInCart);
            } else {
                $product->setAmountInCart($product->getAmount());
                $acc['erorrs'][$product->getId()] = ['Not enough in stock'];
            }

            $acc['products'][] = $product;
            $acc['totalPrice'] += $product->getPrice() * $amountInCart;
            $acc['totalWeight'] += $product->getWeight() * $amountInCart;

            return $acc;
        }, []);

        /** @var \App\Entity\User $user */
        $user = $security->getUser();
        $address = $user?->getAddresses()[0] ?? new Address();
        if ($user) {
            $allShippingMethods = $address->getCountry()->getShippingMethods();
            $freightCost = $freightCostGetter->prepareDataAndGetCost(
                $address,
                $result['totalWeight'],
                $allShippingMethods[0],
            );
            $priceWithDelivery = $freightCost !== null ? $freightCost + $result['totalPrice'] : null;
        }

        return $this->render('cart/index.html.twig', [
            'products' => $result['products'],
            'totalWeight' => $result['totalWeight'],
            'productsTotal' => $result['totalPrice'],
            'treeMap' => [],
            'allMethods' => $allShippingMethods ?? null,
            'currentMethod' => $allShippingMethods[0] ?? null,
            'address' => $address,
            'freightCost' => $freightCost ?? null,
            'totalPrice' => $priceWithDelivery ?? null,
        ]);
    }
}
