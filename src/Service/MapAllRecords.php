<?php

namespace App\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;

class MapAllRecords
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function mapRecords(array $records, bool $ifWithCategories = false)
    {
        return ['categories' => []];

        if (empty($records)) {
            return ['categories' => []];
        }

        // $logger = $this->logger;
        // $collection = new ArrayCollection($records);
        // $result = $collection->reduce(function (array $accumulator, $record) use ($ifWithCategories) {
        //     $company = $record->getBrand();
        //     $price = $record->getPrice();
        //     $type = $record->getType();
        //     $specs = $record->getSpecifications();
        //     $currentMax = $accumulator['price']['max'] ?? 0;
        //     $currentMin = $accumulator['price']['min'] ?? 0;

        //     $accumulator['brand'][$company] = $company;
        //     $accumulator['type'][$type] = $type;
        //     if ($currentMax < $price && $currentMin === 0) {
        //         $accumulator['price']['max'] = $price;
        //         $accumulator['price']['min'] = $currentMax;
        //     } elseif ($currentMax < $price) {
        //         $accumulator['price']['max'] = $price;
        //     } elseif ($currentMin === 0 || $currentMin > $price) {
        //         $accumulator['price']['min'] = $price;
        //     }

        //     foreach ($specs as $spec) {
        //         $property = $spec->getProperty();
        //         $propValue = $spec->getValue();
        //         $accumulator[$property][$propValue] = $propValue;
        //     }

        //     if ($ifWithCategories) {
        //         $categoryId = $record->getCategory()->getId();
        //         $accumulator['categories'][$categoryId] = $categoryId;
        //     }

        //     return $accumulator;
        // }, []);

        // $this->logger->info(print_r($result, true));
        // return $result;
    }
}
