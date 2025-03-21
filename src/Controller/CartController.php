<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\FreightRateRepository;
use App\Repository\FreightRepository;
use App\Repository\ProductRepository;
use App\Repository\ShippingMethodRepository;
use App\Service\FreightPreparator;
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
        FreightPreparator $freightPreparator,
        FreightRateRepository $freightRateRepository,
        LoggerInterface $log,
    ): Response {
        $cart = json_decode($request->cookies->get('cart', '{}'), true);
        $ids = array_keys($cart['ids'] ?? []);
        $products = new ArrayCollection($productRep->findSomeByIds($ids));


        $result = $products->reduce(function ($acc, Product $product) use ($cart): array {
            if (!isset($acc['products'])) {
                $acc['products'] = [];
                $acc['totalPrice'] = 0;
                $acc['totalWeight'] = 0;
            }

            $amountInCart = $cart['ids'][$product->getId()]['amount'] ?? 1;
            $product->setAmountInCart($amountInCart);

            $acc['products'][] = $product;
            $acc['totalPrice'] += $product->getPrice() * $amountInCart;
            $acc['totalWeight'] += $product->getWeight() * $amountInCart;

            return $acc;
        }, []);

        /** @var \App\Entity\User $user */
        $user = $security->getUser();
        if ($user) {
            $allShippingMethods = $user->getAddresses()[0]->getCountry()->getShippingMethods();
            $freightData = $freightPreparator::prepareData(
                $user->getAddresses()[0],
                $result['totalWeight'],
                $allShippingMethods[0],
            );
            $freightPrice = $freightRateRepository->findPriceForAdress($freightData);
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'result' => $result,
            'allShippingMethods' => $allShippingMethods,
            'totalWeight' => $result['totalWeight'],
            'freightData' => $freightData ?? [],
            'freightPrice' => $freightPrice ?? 0,
        ]);
    }
}
