<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Psr\Log\LoggerInterface;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private LoggerInterface $logger)
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

    public function getCategoriesFromSearch(string $value = '', array $cat = [], array $filter = []): array
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

        if ($filter) {
            $i = 2;
            foreach ($filter as $key => $value) {
                $i++;
                if ($key === 'specs') {
                    foreach ($value as $specKey => $specValue) {
                        $query->join('p.specifications', 's')
                        ->andWhere("s.{$specKey} IN (:val{$i})")
                        ->setParameter("val{$i}", $specValue);
                        $i++;
                    }
                } else {
                    $query
                    ->andWhere("p.{$key} IN (:val{$i})")
                    ->setParameter("val{$i}", $value);
                }
            }
        }

        return $query->getQuery()->getResult(AbstractQuery::HYDRATE_SCALAR_COLUMN);
    }

    public function getPaginatedValues(string $value, array $cat, int $page, array $filter, int $maxPerPage = 5): Pagerfanta
    {
        $this->logger->info("paginating: query: {$value}, page: {$page}");
        $this->logger->info(print_r($filter, true));
        $this->logger->info(print_r($cat, true));
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

        if ($filter) {
            $i = 2;
            foreach ($filter as $key => $filterValue) {
                $i++;
                $this->logger->info(print_r($filterValue, true));
                if ($key === 'specs') {
                    foreach ($filterValue as $specKey => $specValue) {
                        $query->join('p.specifications', 's')
                        ->andWhere("s.{$specKey} IN (:val{$i})")
                        ->setParameter("val{$i}", $specValue);
                        $i++;
                    }
                } else {
                    // $this->logger->info('WHYYYYYYYYYYYYYYYYYYYYYYY');
                    // $this->logger->info("Key: {$key}, value: {$value}");
                    $query
                    ->andWhere("p.{$key} IN (:val{$i})")
                    ->setParameter("val{$i}", $filterValue);
                }
            }
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

    public function chooseAllServers()
    {
        $value = 'server';
        return $this->createQueryBuilder('p')
            ->andWhere("p.type = :val")
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult();
    }
}
