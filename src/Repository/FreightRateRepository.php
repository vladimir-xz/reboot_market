<?php

namespace App\Repository;

use App\Dto\FreightDataDto;
use App\Entity\Address;
use App\Entity\FreightRate;
use App\Entity\ShippingMethod;
use App\Entity\Money;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FreightRate>
 */
class FreightRateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FreightRate::class);
    }

    public function findPriceForAdress(
        string $preparedPostcode,
        int $weight,
        int $addressId,
        int $shippingMethodId
    ): ?int {
        //TODO: fetch adress with country beforehand
        $data = $this->createQueryBuilder('f')
            ->select('f.price')
            ->andWhere('f.weight = :val1')
            ->setParameter('val1', $weight)
            ->andWhere('f.postcode = :val2')
            ->setParameter('val2', $preparedPostcode)
            ->andWhere('f.country = :val3')
            ->setParameter('val3', $addressId)
            ->andWhere('f.shippingMethod = :val4')
            ->setParameter('val4', $shippingMethodId)
            ->getQuery()
            ->getOneOrNullResult();

        return $data['price'] ?? null;
    }
}
