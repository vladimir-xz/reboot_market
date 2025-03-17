<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\NullAdapter;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Psr\Log\LoggerInterface;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    private array $allowedFilters;

    public function __construct(ManagerRegistry $registry, private LoggerInterface $logger)
    {
        parent::__construct($registry, Product::class);
        $this->allowedFilters = [
            'Height' => 'Height',
            'Form-factor' => 'Form-factor',
            'Generation' => 'Generation',
            'price' => 'price',
            'type' => 'type',
            'brand' => 'brand',
            'product' => ['brand', 'type', 'price'],
            'specs' => ['Form-factor', 'Height', 'Generation']
        ];
    }

    public function findOneByIdJoinedToAllRelatedTables(int $productId): ?Product
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.specifications', "s")
            ->leftJoin('p.images', "i")
            ->leftJoin('p.related', "r")
            ->leftJoin('r.images', 'ri')
            ->leftJoin('p.description', "d")
            ->select('p', 's', 'i', 'PARTIAL r.{id, name, price}', 'ri', 'd')
            ->andWhere('p.id = :val')
            ->setParameter('val', $productId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
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

    public function getAllProductsWithCategoryAndFilters(string $query = '', array $catInclude = [], array $catExclude = [], array $filters = []): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.specifications', "s")
            ->select('p', 'c', 's');

        if ($query !== '') {
            $qb = $this->addQuerySearch($qb, $query);
        }

        if ($catInclude) {
            $qb = $this->addIncludeCategoriesSearch($qb, $catInclude);
        }

        if ($catExclude) {
            $qb = $this->addExcludeCategoriesSearch($qb, $catExclude);
        }

        if ($filters) {
            $qb = $this->addFilters($qb, $filters);
        }

        return $qb->getQuery()->getResult();
    }

    public function getPaginatedValues(string $query, array $catInclude, array $catExclude, array $filters, int $page, int $maxPerPage = 12): Pagerfanta
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.images', 'i')
            ->select('p', 'i')
            ->orderBy('p.id', 'ASC');

        if ($query !== '') {
            $qb = $this->addQuerySearch($qb, $query);
        }

        if ($catInclude) {
            $qb = $this->addIncludeCategoriesSearch($qb, $catInclude);
        }

        if ($catExclude) {
            $qb = $this->addExcludeCategoriesSearch($qb, $catExclude);
        }

        if ($filters) {
            $qb = $this->addFilters($qb, $filters);
        }


        $adapter = new QueryAdapter($qb);
        $pagerfanta = new Pagerfanta($adapter);

        $pagerfanta->setMaxPerPage($maxPerPage);
        $pagerfanta->setCurrentPage($page);

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

    public function getRecentlyAdded(int $maxResult = 10)
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.images', "i")
            ->select('p', 'i')
            ->orderBy('p.createdAt', 'DESC')
        ;

        $adapter = new QueryAdapter($qb);
        $pagerfanta = new Pagerfanta($adapter);

        $pagerfanta->setMaxPerPage($maxResult);

        return $pagerfanta;
    }

    private function addQuerySearch(QueryBuilder $qb, string $query): QueryBuilder
    {
        return $qb
            ->andWhere('LOWER(p.name) LIKE :val')
            ->setParameter('val', strtolower('%' . $query . '%'));
    }

    private function addIncludeCategoriesSearch(QueryBuilder $qb, array $catInclude): QueryBuilder
    {
        return $qb
            ->andWhere('p.category IN (:val2)')
            ->setParameter('val2', $catInclude);
    }

    private function addExcludeCategoriesSearch(QueryBuilder $qb, array $catExclude): QueryBuilder
    {
        return $qb
            ->andWhere('p.category NOT IN (:val3)')
            ->setParameter('val3', $catExclude);
    }

    private function addFilters(QueryBuilder $qb, array $filters): QueryBuilder
    {
        $i = 4;
        $j = 5;

        foreach ($filters as $key => $filterValues) {
            if (!array_key_exists($key, $this->allowedFilters)) {
                throw new Exception('Not allowed key value - ' . $key);
            }

            if ($key === 'price') {
                $j = $i + 1;
                $qb->andWhere("p.price BETWEEN :val{$i} AND :val{$j}")
                    ->setParameter("val{$i}", $filterValues['min'])
                    ->setParameter("val{$j}", $filterValues['max']);
            } elseif (in_array($key, $this->allowedFilters['product'])) {
                $qb->andWhere("p.{$key} IN (:val{$i})")
                    ->setParameter("val{$i}", $filterValues);
            } else {
                $qb->leftJoin('p.specifications', "s{$i}");
                $qb->andWhere("s{$i}.property = :val{$i} AND s{$i}.value IN (:val{$j})")
                    ->setParameter("val{$i}", $key)
                    ->setParameter("val{$j}", $filterValues);
            }
            $i += 2;
            $j += 2;
        }

        return $qb;
    }
}
