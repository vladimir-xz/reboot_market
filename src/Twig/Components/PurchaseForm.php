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
use App\Dto\PaymentDataDto;
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

    #[LiveProp]
    public ?int $productsTotal = null;

    #[LiveProp]
    public ?int $totalPrice = null;

    #[LiveProp]
    public ?int $freightCost = null;

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

    #[LiveProp]
    public ?bool $isFreightCostSet = false;

    public function __construct(
        private FreightCostGetter $freightCostGetter,
        private CountryRepository $countryRepository,
        private NormalizerInterface $serializer,
        private LoggerInterface $log,
    ) {
        $this->address = new Address();
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
            $this->isFreightCostSet = false;
            return;
        }

        // if (!$this->validateField('address', false)) {
        //     $this->isFreightCostSet = false;
        //     return;
        // }

        $this->freightCost = $this->freightCostGetter->prepareDataAndGetCost(
            $this->address->getPostcode(),
            $this->country->getId(),
            $this->totalWeight,
            $this->shippingMethod->getId(),
        );

        if ($this->isFreightCostSet = $this->freightCost !== null) {
            $this->totalPrice = $this->freightCost + $this->productsTotal;
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
        $data = new PaymentDataDto(
            $this->address,
            $this->country,
            $this->totalWeight,
            $this->shippingMethod,
        );

        return $this->serializer->normalize($data, 'array');
    }
}
