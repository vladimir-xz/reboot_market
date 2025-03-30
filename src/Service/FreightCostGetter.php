<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\Country;
use App\Entity\Product;
use App\Dto\FreightDataDto;
use App\Dto\PaymentDataDto;
use App\Entity\ShippingMethod;
use App\Repository\FreightRateRepository;
use Exception;

final class FreightCostGetter
{
    public function __construct(private FreightRateRepository $freightRateRepository)
    {
    }

    public function prepareDataAndGetCost(string $postcode, int $countryId, int $weight, int $shippingMethodId)
    {
        $roundedWeight = match (true) {
            $weight <= 30 => 30,
            $weight <= 50 => 50,
            $weight <= 100 => 100,
            $weight <= 2500 => ceil($weight / 100) * 100,
            default => throw new Exception('Too heavy to transport'),
        };

        $preparedPostcode = substr($postcode, 0, 2);

        return $this->freightRateRepository->findPriceForAdress(
            $preparedPostcode,
            $roundedWeight,
            $countryId,
            $shippingMethodId
        );
    }

    public function getCostFromPaymentDto(PaymentDataDto $paymentDataDto)
    {
        return $this->prepareDataAndGetCost(
            $paymentDataDto->getAddress()['postcode'],
            $paymentDataDto->getCountry()['id'],
            $paymentDataDto->getWeight(),
            $paymentDataDto->getShippingMethod()['id'],
        );
    }
}
