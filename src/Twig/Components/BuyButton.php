<?php

namespace App\Twig\Components;

use App\Entity\Product;
use App\Dto\CartDto;
use App\Service\CartProductHandler;
use Psr\Log\LoggerInterface;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class BuyButton
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    #[LiveProp]
    public Product $product;

    public function __construct(
        private RequestStack $requestStack,
        private CartProductHandler $cartProductHandler,
        private LoggerInterface $log
    ) {
    }

    #[LiveAction]
    public function save(#[LiveArg] int $amount)
    {
        $this->product->setAmountInCart($amount);
        $session = $this->requestStack->getCurrentRequest()->getSession();
        $cart = $session->get('cart', new CartDto());
        $newCart = $this->cartProductHandler::add($cart, $this->product, $this->log);
        $session->set('cart', $newCart);

        $this->emit('productAdded');
    }
}
