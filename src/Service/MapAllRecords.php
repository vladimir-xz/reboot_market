<?php

namespace App\Service;

use Doctrine\Common\Collections\ArrayCollection;

class MapAllRecords
{
    public static function mapRecords(array $records, bool $ifWithCategoriesAndCount = false)
    {
        $collection = new ArrayCollection($records);
        return $collection->reduce(function (array $accumulator, $record) use ($ifWithCategoriesAndCount) {
            $company = $record->getBrand();
            $price = $record->getPrice();
            $type = $record->getType();
            $specs = $record->getSpecifications();
            $currentMax = $accumulator['price']['max'] ?? 0;
            $currentMin = $accumulator['price']['min'] ?? 0;

            $accumulator['brand'][$company] = $company;
            $accumulator['type'][$type] = $type;
            if ($currentMax < $price && $currentMin === 0) {
                $accumulator['price']['max'] = $price;
                $accumulator['price']['min'] = $currentMax;
            } elseif ($currentMax < $price) {
                $accumulator['price']['max'] = $price;
            } elseif ($currentMin === 0 || $currentMin > $price) {
                $accumulator['price']['min'] = $price;
            }

            foreach ($specs as $spec) {
                $property = $spec->getProperty();
                $propValue = $spec->getValue();
                $accumulator[$property][$propValue] = $propValue;
            }

            if ($ifWithCategoriesAndCount) {
                $count = $accumulator['count'] ?? 0;
                $categoryId = $record->getCategory()->getId();
                $accumulator['categories'][$categoryId] = $categoryId;
                $count++;
                $accumulator['count'] = $count;
            }



            return $accumulator;
        }, []);
    }
}
