<?php

namespace App\Repository;

use App\Dto\FreightDataDto;
use App\Entity\FreightRate;
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

    public function findPriceForAdress(FreightDataDto $freightData)
    {
        //TODO: fetch adress with country beforehand
        return $this->createQueryBuilder('f')
            ->select('f.price')
            ->andWhere('f.weight = :val1')
            ->setParameter('val1', $freightData->getWeight())
            ->andWhere('f.postcode = :val2')
            ->setParameter('val2', $freightData->getPostcode())
            ->andWhere('f.country = :val3')
            ->setParameter(':val3', $freightData->getCountry())
            ->andWhere('f.shippingMethod = :val4')
            ->setParameter('val4', $freightData->getShippingMethod())
            ->getQuery()
            ->getSingleColumnResult();
    }

    //    /**
    //     * @return FreightRate[] Returns an array of FreightRate objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?FreightRate
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
