<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Doctrine\ORM\QueryAdapter;

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
        $query = $this->createQueryBuilder('p')
            ->andWhere('LOWER(p.name) LIKE :val')
            ->setParameter('val', strtolower('%' . $value . '%'))
        ;

        if ($cat) {
            $query
            ->andWhere('p.category IN (:val2)')
            ->setParameter('val2', $cat);
        }

        return $query->getQuery()->getResult();
    }

    public function getCategoriesFromSearch($value = '', $cat = []): array
    {
        $em = $this->getEntityManager();
        $query = $this->createQueryBuilder('p')
            ->select('DISTINCT c.id')
            ->join('p.category', 'c')
            ->where('LOWER(p.name) LIKE :val')
            ->setParameter('val', strtolower('%' . $value . '%'))
        ;

        if ($cat) {
            $query
            ->andWhere('p.category IN (:val2)')
            ->setParameter('val2', $cat);
        }

        return $query->getQuery()->getResult(AbstractQuery::HYDRATE_SCALAR_COLUMN);
    }

    public function getPaginatedValues(string $value, array $cat, int $page, int $maxPerPage = 5): Pagerfanta
    {
        $query = $this->createQueryBuilder('p')
            ->andWhere('LOWER(p.name) LIKE :val')
            ->setParameter('val', strtolower('%' . $value . '%'))
            ->orderBy('p.id', 'ASC');
        ;

        if ($cat) {
            $query
            ->andWhere('p.category IN (:val2)')
            ->setParameter('val2', $cat);
        }

        $adapter = new QueryAdapter($query);
        $pagerfanta = new Pagerfanta($adapter);

        $pagerfanta->setMaxPerPage($maxPerPage);
        $pagerfanta->setCurrentPage($page);
        // $pagerfanta->setMaxNbPages($maxNbPages);

        return $pagerfanta;
    }

    public function getAllWithSpecs()
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.specifications', 's')
            ->addSelect('s')
            ->getQuery()
            ->getResult()
        ;
    }
}
