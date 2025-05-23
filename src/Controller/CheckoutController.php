<?php

namespace App\Controller;

use App\Dto\CartDto;
use App\Entity\Address;
use App\Entity\Product;
use App\Dto\ShippingDataDto;
use App\Repository\CountryRepository;
use App\Repository\FreightRateRepository;
use App\Repository\FreightRepository;
use App\Repository\ProductRepository;
use App\Repository\ShippingMethodRepository;
use App\Service\FreightCostGetter;
use App\Service\PriceHandler;
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
    #[Route('/checkout', name: 'checkout.index')]
    public function index(
        Request $request,
        Security $security,
        FreightCostGetter $freightCostGetter,
        LoggerInterface $log,
    ): Response {
        /** @var \App\Dto\CartDto $cart */
        $cart = $request->getSession()->get('cart', new CartDto());
        if ($cart->getTotalPrice() === 0) {
            return $this->render('cart/index.html.twig', [
                'cart' => $cart,
                'productsTotal' => null,
                'idsAndAmounts' => [],
                'address' => new Address(),
                'allMethods' => null,
                'currentMethod' => null,
                'totalWeight' => null,
                'freightCost' => null,
                'totalPrice' => null,
            ]);
        }


        /** @var \App\Entity\User $user */
        $user = $security->getUser();
        $address = $user?->getAddresses()[0] ?? new Address();
        $productsTotal = $cart->getTotalPrice();

        if ($user) {
            $allShippingMethods = $address->getCountry()->getShippingMethods();
            $freightCost = $freightCostGetter->prepareDataAndGetCost(
                $address->getPostcode(),
                $address->getCountry()->getId(),
                $cart->getTotalWeight(),
                $allShippingMethods[0]->getId(),
            );
            if ($freightCost !== null) {
                $priceWithDelivery = $freightCost + $productsTotal;
            }
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'totalWeight' => $cart->getTotalWeight(),
            'productsTotal' => $productsTotal,
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
        #[MapQueryString] ShippingDataDto $paymentDto = new ShippingDataDto(),
    ) {
        $json = $serializer->serialize($paymentDto, 'json');

        return $this->render('cart/show.html.twig', [
            'paymentData' => $json,
            'treeMap' => [],
        ]);
    }

    #[Route('/create-checkout-session', name: 'checkout.session')]
    public function checkout(
        Request $request,
        FreightCostGetter $freightCostGetter,
        ProductRepository $productRepository,
        PriceHandler $priceHandler,
        #[MapRequestPayload] ShippingDataDto $shippingDataDto,
    ) {
        $currency = $request->getSession()->get('currency', 'czk');
        $cart = $request->getSession()->get('cart', 'czk');
        $products = $cart->getIdsAndProducts();
        $freightCost = $freightCostGetter->getCostFromPaymentDto($shippingDataDto);
        $freightCostForCurrency = $priceHandler->convertToCurrency($freightCost, $currency);

        $collection = new ArrayCollection($productRepository->findSomeByIds(array_keys($products)));
        $productsAndPrices = $collection->map(function (Product $product) use ($cart, $currency, $priceHandler) {
            $amountInCart = $cart->getAmountOfProduct($product->getId());
            $amount = $product->getAmount() < $amountInCart || $amountInCart < 1
                ? $product->getAmount()
                : $amountInCart;

            return [
                'price_data' => [
                    'currency' => $currency,
                    'unit_amount' => round($priceHandler->convertToCurrency($product->getPrice(), $currency)),
                    'product_data' => ['name' => $product->getName()],
                ],
                'quantity' => $amount,
            ];
        })->toArray();

        $returnUrl = $this->generateUrl('checkout.return', ['session_id' => ''], UrlGeneratorInterface::ABSOLUTE_URL);
        $stripe = new StripeClient(["api_key" => $this->getParameter('app.stripeKey')]);
        $checkoutSession = $stripe->checkout->sessions->create([
            'line_items' => $productsAndPrices,
            'shipping_options' => [
                [
                    'shipping_rate_data' => [
                        'type' => 'fixed_amount',
                        'display_name' => $shippingDataDto->getShippingMethod()['name'],
                        'fixed_amount' => [
                            'amount' => round($freightCostForCurrency),
                            'currency' => $currency,
                        ],
                        'metadata' => [
                            'postcode' => $shippingDataDto->getAddress()['postcode'],
                            'country' => $shippingDataDto->getCountry()['name'],
                            'firstLine' => $shippingDataDto->getAddress()['firstLine'],
                            'secondLine' => $shippingDataDto->getAddress()['secondLine'],
                            'town' => $shippingDataDto->getAddress()['town'],
                        ]
                    ]
                ]
            ],
            'phone_number_collection' => [
                'enabled' => true
            ],
            'custom_fields' => [
                [
                  'key' => 'recipient',
                  'label' => [
                    'type' => 'custom',
                    'custom' => 'Name/Organization',
                  ],
                  'type' => 'text',
                ],
              ],
            'mode' => 'payment',
            'ui_mode' => 'embedded',
            'return_url' => $returnUrl . '{CHECKOUT_SESSION_ID}'
        ]);

        return $this->json(['clientSecret' => $checkoutSession->client_secret]);
    }

    #[Route('/checkout/return', name: 'checkout.return')]
    public function return(
        Request $request,
        #[MapQueryParameter] string $session_id,
    ) {
        $stripe = new \Stripe\StripeClient($this->getParameter('app.stripeKey'));
        $session = $stripe->checkout->sessions->retrieve(
            $session_id,
            []
        );

        if ($session->status == 'open') {
            $stripe->checkout->sessions->expire(
                $session_id,
                []
            );
            $this->redirectToRoute('checkout.index');
        } elseif ($session->status == 'complete') {
            $paymentStatus = $session->payment_status;
            $customer = $session->customer_details;
            $name = $session->custom_fields[0]->text->value;
            $shippingRate = $session->shipping_options[0]->shipping_rate;
            $shippingData = $stripe->shippingRates->retrieve($shippingRate);
            // $stripe->shippingRates->update($shippingRate, ['metadata' => ['recipient' => $name]]);
            $listItems = $stripe->checkout->sessions->allLineItems(
                $session_id,
                ['limit' => 100]
            );
        }


        return $this->render('cart/return.html.twig', [
            'session' => $session,
            'customer' => $customer,
            'shippingData' => $shippingData,
            'paymentStatus' => $paymentStatus,
            'shippingData' => $shippingData,
            'listItems' => $listItems ?? [],
            'treeMap' => [],
        ]);
    }
}
