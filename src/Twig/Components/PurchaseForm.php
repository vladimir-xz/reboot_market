<?php

namespace App\Twig\Components;

use App\Form\AddressType;
use App\Entity\Address;
use App\Entity\Country;
use App\Entity\FreightRate;
use App\Entity\ShippingMethod;
use App\Repository\CountryRepository;
use App\Repository\FreightRateRepository;
use App\Service\FreightCostGetter;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\PostHydrate;
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
    public ?int $freightCost = null;

    #[LiveProp]
    /** @var ShippingMethod[] */
    public $shippingMethods = [];

    #[LiveProp(writable: true, onUpdated: 'onCountryUpdate')]
    #[Assert\NotBlank]
    public Country $country;

    #[LiveProp(writable: true, onUpdate: 'onRelevantUpdate')]
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
    public ?bool $isFreightCostSet = false;

    public function __construct(
        private FreightCostGetter $freightCostGetter,
        private CountryRepository $countryRepository,
        private LoggerInterface $log,
    ) {
        $this->address = new Address();
        $this->country = new Country();
    }

    public function onIrrelevantUpdate()
    {
        if ($this->isFreightCostSet) {
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
        if (!$this->isValid()) {
            return;
        }

        //IF OLD SHIPPING METHOD IN ARRAY, KEEP IT, IF ITS NOT OR NULL, CHANGE
        $this->shippingMethods = $this->address->getCountry()->getShippingMethods();
        $this->shippingMethod = $this->shippingMethod ?? $this->shippingMethods[0];
        $this->freightCost = $this->freightCostGetter->prepareDataAndGetCost(
            $this->address,
            $this->totalWeight,
            $this->shippingMethod,
        );
        $this->isFreightCostSet = $this->freightCost !== null;
        $this->totalPrice = $this->isFreightCostSet ? $this->freightCost + $this->productsTotal : null;
    }

    public function getCountries()
    {
        return $this->countryRepository->getAll();
    }

    // public function getProductsTotal()
    // {
    //     return $this->productsTotal;
    // }

    // public function getfreightCost()
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
    //     $this->freightCost = $freightRate['price'];
    //     $this->totalPrice = $this->freightCost + $this->productsTotal;

    //     return $this->freightCost;
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
