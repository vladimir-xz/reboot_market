<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\Country;
use App\Entity\Product;
use App\Dto\FreightDataDto;
use App\Entity\ShippingMethod;
use Exception;

final class FreightPreparator
{
    public static function prepareData(Address $address, int $weight, ShippingMethod $shippingMethod)
    {
        $roundedWeight = match (true) {
            $weight <= 30 => 30,
            $weight <= 50 => 50,
            $weight <= 100 => 100,
            $weight <= 2500 => ceil($weight / 100) * 100,
            default => throw new Exception('Too have to transport'),
        };

        $preparedPostcode = substr($address->getPostcode(), 0, 2);

        return new FreightDataDto($preparedPostcode, $roundedWeight, $address->getCountry(), $shippingMethod);
    }
}
