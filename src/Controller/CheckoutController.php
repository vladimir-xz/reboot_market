<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Product;
use App\Dto\PaymentDataDto;
use App\Repository\CountryRepository;
use App\Repository\FreightRateRepository;
use App\Repository\FreightRepository;
use App\Repository\ProductRepository;
use App\Repository\ShippingMethodRepository;
use App\Service\FreightCostGetter;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CheckoutController extends AbstractController
{
    #[Route('/checkout', name: 'checkout')]
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
                'productsIds' => [],
                'address' => new Address(),
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
                $acc['idsAndAmounts'] = [];
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
            $acc['idsAndAmounts'][$product->getId()] = $product->getAmountInCart();
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
            'idsAndAmounts' => $result['idsAndAmounts'],
            'treeMap' => [],
            'allMethods' => $allShippingMethods ?? null,
            'currentMethod' => $allShippingMethods[0] ?? null,
            'address' => $address,
            'freightCost' => $freightCost ?? null,
            'totalPrice' => $priceWithDelivery ?? null,
        ]);
    }

    #[Route('/checkout/show', name: 'checkout.show')]
    public function show(
        Request $request,
        SerializerInterface $serializer,
        ProductRepository $productRepository,
        #[MapQueryString] PaymentDataDto $paymentDto = new PaymentDataDto(),
    ) {
        $ids = array_keys($paymentDto->getProducts() ?? []);
        $products = new ArrayCollection($productRepository->findSomeByIds($ids));
        $result = $products->map(function (Product $product) use ($paymentDto) {
            $id = $product->getId();
            $name = $product->getName();
            $amountInCart = $paymentDto->getProducts()[$id];
            $price = $product->getPrice();
            if ($product->hasEnoughInStockAndNotNegative($amountInCart)) {
                $amount = $amountInCart;
            } else {
                $amount = $product->getAmount();
            }

            return [
                'id' => $id,
                'name' => $name,
                'amount' => $amount,
                'price' => $price,
            ];
        })->toArray();

        $controlledDataDto = new PaymentDataDto(
            $paymentDto->getPostcode(),
            $paymentDto->getCountryId(),
            $paymentDto->getWeight(),
            $paymentDto->getShippingMethodId(),
            $result,
        );

        $json = $serializer->serialize($controlledDataDto, 'json');

        return $this->render('cart/show.html.twig', [
            'paymentData' => $json,
            'treeMap' => [],
        ]);
    }

    #[Route('/create-checkout-session', name: 'checkout.session')]
    public function checkout(
        Request $request,
        LoggerInterface $logger,
        #[MapRequestPayload] PaymentDataDto $paymentDto,
    ) {
        $logger->info('Its working');
        $collection = new ArrayCollection($paymentDto->getProducts());
        $productsAndPrices = $collection->map(function ($product) {
            return [
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => $product['price'],
                    'product_data' => ['name' => $product['name']],
                ],
                'quantity' => $product['amount'],
            ];
        })->toArray();

        $stripe = new StripeClient(["api_key" => $this->getParameter('app.stripeKey')]);
        $checkoutSession = $stripe->checkout->sessions->create([
            'line_items' => $productsAndPrices,
            'mode' => 'payment',
            'ui_mode' => 'embedded',
            'return_url' => $this->generateUrl('checkout.return', ['session_id' => '{CHECKOUT_SESSION_ID}'], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->json(['clientSecret' => $checkoutSession->client_secret]);
    }

    #[Route('/checkout/return', name: 'checkout.return')]
    public function return(
        Request $request,
        #[MapQueryParameter] string $sessionId,
    ) {
        return $this->render('cart/show.html.twig', [
            'sessionId' => $sessionId,
            'treeMap' => [],
        ]);
    }
}
