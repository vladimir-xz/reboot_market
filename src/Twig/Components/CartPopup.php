<?php

namespace App\Twig\Components;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class CartPopup extends AbstractController
{
    use DefaultActionTrait;

    public ?\stdClass $cart;
    public ?int $total;

    public function __construct(private RequestStack $requestStack, private LoggerInterface $logger)
    {
    }

    public function setProductsAndTotal()
    {
        $this->cart = json_decode($this->requestStack->getCurrentRequest()->cookies->get('cart', ''));
        $this->total = $this->cart?->total ?? 0;
    }

    public function getProducts()
    {
        return (array) $this->cart?->ids ?? 'Cart is emty';
    }

    public function getTotal()
    {
        return $this->total;
    }
}
