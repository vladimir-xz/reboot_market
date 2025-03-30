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
    #[Route('/checkout', name: 'checkout.index')]
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
                'idsAndAmounts' => [],
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

            $id = $product->getId();
            $amountInCart = $cart['ids'][$id]['amount'] ?? 1;

            if ($product->hasEnoughInStockAndNotNegative($amountInCart)) {
                $product->setAmountInCart($amountInCart);
            } else {
                $product->setAmountInCart($product->getAmount());
                $acc['erorrs'][$id] = ['Not enough in stock'];
            }

            $acc['products'][] = $product;
            $acc['idsAndAmounts'][$id] = [
                'id' => $id,
                'amount' => $product->getAmountInCart()
            ];
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
                $address->getPostcode(),
                $address->getCountry()->getId(),
                $result['totalWeight'],
                $allShippingMethods[0]->getId(),
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
        #[MapRequestPayload] PaymentDataDto $paymentDto,
    ) {
        $freightCost = $freightCostGetter->getCostFromPaymentDto($paymentDto);
        $products = $paymentDto->getProducts() ?? [];
        $collection = new ArrayCollection($productRepository->findSomeByIds(array_keys($products)));
        $productsAndPrices = $collection->map(function (Product $product) use ($products) {
            $amountInCart = $products[$product->getId()]['amount'];
            $amount = $product->hasEnoughInStockAndNotNegative($amountInCart) ? $amountInCart : $product->getAmount();

            return [
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => $product->getPrice(),
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
                        'display_name' => $paymentDto->getShippingMethod()['name'],
                        'fixed_amount' => [
                            'amount' => $freightCost,
                            'currency' => 'usd',
                        ],
                        'metadata' => [
                            'postcode' => $paymentDto->getAddress()['postcode'],
                            'country' => $paymentDto->getCountry()['name'],
                            'firstLine' => $paymentDto->getAddress()['firstLine'],
                            'secondLine' => $paymentDto->getAddress()['secondLine'],
                            'town' => $paymentDto->getAddress()['town'],
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
