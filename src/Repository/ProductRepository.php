<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
    * @return Product[] Returns an array of Product objects
    */
    public function findByNameField(string $value, array $cat): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('LOWER(p.name) LIKE :val')
            ->andWhere('p.category IN :val2')
            ->setParameter('val', strtolower('%' . $value . '%'))
            ->setParameter('val2', $cat)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getCategoriesFromSearch($value): array
    {
        return $this->createQueryBuilder('p')
            ->select('DISTINCT c.id')
            ->join('p.category', 'c')
            ->where('LOWER(p.name) LIKE :val')
            ->setParameter('val', strtolower('%' . $value . '%'))
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_SCALAR_COLUMN)
        ;
    }
}
