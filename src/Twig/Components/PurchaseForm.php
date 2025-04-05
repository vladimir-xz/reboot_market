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
use App\Dto\ShippingDataDto;
use App\Entity\Money;
use Symfony\UX\LiveComponent\Hydration\DoctrineEntityHydrationExtension;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ValidatableComponentTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AsLiveComponent]
final class PurchaseForm extends AbstractController
{
    use DefaultActionTrait;
    use ValidatableComponentTrait;

    #[LiveProp]
    public ?int $totalWeight = null;

    #[LiveProp(hydrateWith: 'hydrateMoney')]
    public ?Money $productsTotal = null;

    #[LiveProp(hydrateWith: 'hydrateMoney')]
    public ?Money $totalPrice = null;

    #[LiveProp(hydrateWith: 'hydrateMoney')]
    public ?Money $freightCost = null;

    #[LiveProp(writable: true, onUpdated: 'onCountryUpdate')]
    #[Assert\NotBlank]
    public ?Country $country = null;

    #[LiveProp(writable: true, onUpdated: 'onRelevantUpdate')]
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

    public function __construct(
        private FreightCostGetter $freightCostGetter,
        private CountryRepository $countryRepository,
        private NormalizerInterface $serializer,
        private LoggerInterface $log,
    ) {
    }

    public function onIrrelevantUpdate()
    {
        if ($this->isFreightCostSet()) {
            return;
        }

        $this->onRelevantUpdate();
    }

    public function onCountryUpdate()
    {
        if ($this->country === null) {
            return;
        }

        $this->address->setCountry($this->country);
        //IF OLD SHIPPING METHOD IN ARRAY, KEEP IT, IF ITS NOT OR NULL, CHANGE
        $this->shippingMethod = $this->country->getShippingMethods()[0];
        $this->onRelevantUpdate();
    }

    public function onRelevantUpdate()
    {
        if ($this->componentValidator->validate($this->address)) {
            return;
        }

        // if (!$this->validateField('address', false)) {
        //     return;
        // }

        $this->freightCost = $this->freightCostGetter->prepareDataAndGetCost(
            $this->address->getPostcode(),
            $this->country->getId(),
            $this->totalWeight,
            $this->shippingMethod->getId(),
        )?->setCurrency($this->productsTotal->getCurrency());

        if ($this->isFreightCostSet()) {
            $totalPriceCount = new Money($this->freightCost, $this->productsTotal->getCurrency());
            $totalPriceCount->addFigure($this->productsTotal);
            $this->totalPrice = $totalPriceCount;
        }
    }

    public function getCountries()
    {
        return $this->countryRepository->findAll();
    }

    public function getShippingMethods()
    {
        return $this->country?->getShippingMethods();
    }

    public function getPaymentData()
    {
        $data = new ShippingDataDto(
            $this->address,
            $this->country,
            $this->totalWeight,
            $this->shippingMethod,
        );

        return $this->serializer->normalize($data, 'array');
    }

    public function isFreightCostSet()
    {
        return $this->freightCost !== null;
    }

    public function hydrateMoney($data): ?Money
    {
        if ($data === null) {
            return null;
        }
        return new Money($data['figure'], $data['currency']);
    }
}
