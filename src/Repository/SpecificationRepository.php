<?php

namespace App\Repository;

use App\Entity\Specification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @extends ServiceEntityRepository<Specification>
 */
class SpecificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Specification::class);
    }

    public function getAllGroupedByProperty()
    {
        $allRecords = $this->findAll();
        $collection = new ArrayCollection($allRecords);
        return $collection->reduce(function (array $acc, object $record): array {
            return $acc[$record->getProperty()] = [$record->getValue() => $record->getId()];
        });
    }

    //    /**
    //     * @return Specification[] Returns an array of Specification objects
    //     */
    //    public function findByExampleField($value): array
    //    {
        //    return $this->createQueryBuilder('s')
        //        ->andWhere('s.exampleField = :val')
        //        ->setParameter('val', $value)
        //        ->orderBy('s.id', 'ASC')
        //        ->setMaxResults(10)
        //        ->getQuery()
        //        ->getResult()
        //    ;
    //    }

    //    public function findOneBySomeField($value): ?Specification
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
