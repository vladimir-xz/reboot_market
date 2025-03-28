<?php

namespace App\Twig\Components;

use App\Form\AddressType;
use App\Entity\Address;
use App\Entity\Country;
use App\Entity\FreightRate;
use App\Entity\ShippingMethod;
use App\Repository\CountryRepository;
use App\Repository\FreightRateRepository;
use App\Service\FreightPreparator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\PreReRender;
use Symfony\UX\LiveComponent\ValidatableComponentTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class PurchaseForm extends AbstractController
{
    use DefaultActionTrait;
    use ValidatableComponentTrait;

    #[LiveProp]
    public ?int $totalWeight = null;

    #[LiveProp]
    public ?int $productsTotal = null;

    #[LiveProp]
    public ?int $totalPrice = null;

    #[LiveProp]
    public ?int $freightPrice = null;

    #[LiveProp(writable: true)]
    /** @var Country[] */
    public $countries = [];

    #[LiveProp(writable: true)]
    /** @var ShippingMethod[] */
    public $shippingMethods = [];

    #[LiveProp(writable: true, onUpdated: 'onCountryUpdate')]
    public Country $country;

    #[LiveProp(writable: true)]
    #[Assert\Valid]
    public ?ShippingMethod $shippingMethod = null;

    #[LiveProp(
        writable: ['firstLine', 'secondLine', 'town', 'postcode'],
        onUpdated: [
            'firstLine' => 'onIrrelevantUpdate',
            'secondLine' => 'onIrrelevantUpdate',
            'town' => 'onIrrelevantUpdate',
            'postcode' => 'onRelevantUpdate'
        ]
    )]
    #[Assert\Valid]
    public ?Address $address;

    #[LiveProp]
    public ?bool $isSuccessful = false;

    public function __construct(
        private FreightPreparator $freightPreparator,
        private FreightRateRepository $freightRateRepository,
        private CountryRepository $countryRepository,
        private LoggerInterface $log,
    ) {
        $this->address = new Address();
        $this->country = new Country();
    }

    // public function postMount(): void
    // {
    //     $this->log->info('Doing post mount');
    //     if ($this->address?->getCountry()) {
    //         $this->countryId = $this->address->getCountry()->getId();
    //     }
    // }

    public function onIrrelevantUpdate()
    {
        if ($this->isSuccessful) {
            return;
        }

        $this->onRelevantUpdate();
    }

    public function onCountryUpdate()
    {
        $this->address->setCountry($this->country);
        $this->onRelevantUpdate();
    }

    public function onRelevantUpdate()
    {
        if ($this->componentValidator->validate($this->address)) {
            $this->isSuccessful = false;
            return;
        }

        $this->isSuccessful = true;
        $this->shippingMethods = $this->address?->getCountry()?->getShippingMethods();
        $this->shippingMethod = $this->shippingMethods[0];
        $freightData = $this->freightPreparator::prepareData(
            $this->address,
            $this->totalWeight,
            $this->shippingMethod,
        );
        $freightRate = $this->freightRateRepository->findPriceForAdress($freightData);
        $this->freightPrice = $freightRate['price'] ?? 0;
        $this->totalPrice = $this->freightPrice + $this->productsTotal;
    }

    // public function getProductsTotal()
    // {
    //     return $this->productsTotal;
    // }

    // public function getFreightPrice()
    // {
    //     if (!$this->isSuccessful) {
    //         return 'Set your address';
    //     }
    //     $this->log->info('Successful');

    //     $freightData = $this->freightPreparator::prepareData(
    //         $this->address,
    //         $this->totalWeight,
    //         $this->shippingMethod,
    //     );
    //     $freightRate = $this->freightRateRepository->findPriceForAdress($freightData);
    //     $this->freightPrice = $freightRate['price'];
    //     $this->totalPrice = $this->freightPrice + $this->productsTotal;

    //     return $this->freightPrice;
    // }

    // public function getTotalPrice()
    // {
    //     if (!$this->totalPrice) {
    //         return 'Set your address and shipping method';
    //     }
    //     return $this->totalPrice;
    // }

    // public function isSuccessful()
    // {
    //     return !is_null($this->totalPrice);
    // }
}
