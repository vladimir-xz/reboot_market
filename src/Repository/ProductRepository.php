<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
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
            'price' => 'price',
            'type' => 'type',
            'brand' => 'brand',
            'product' => ['brand', 'type', 'price'],
            'specs' => ['Form-factor', 'Height']
        ];
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

    public function getCategoriesFromSearch(string $query = '', array $catInclude = [], array $catExclude = [], array $filters = []): array
    {
        if ($query === '' && empty($catInclude) && empty($catInclude) && empty($filters)) {
            return [];
        }

        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->select('DISTINCT c.id');

        if ($query !== '') {
            $qb
            ->andWhere('LOWER(p.name) LIKE :val')
            ->setParameter('val', strtolower('%' . $query . '%'));
        }

        if ($catInclude) {
            $qb
            ->andWhere('p.category IN (:val2)')
            ->setParameter('val2', $catInclude);
        }

        if ($catExclude) {
            $qb
            ->andWhere('p.category NOT IN (:val3)')
            ->setParameter('val3', $catExclude);
        }

        if ($filters) {
            $i = 4;
            $j = 5;

            foreach ($filters as $key => $filterValues) {
                if (!array_key_exists($key, $this->allowedFilters)) {
                    throw new Exception('Not allowed key value');
                }

                if ($key === 'price') {
                    $j = $i + 1;
                    $qb->andWhere("p.price BETWEEN :val{$i} AND :val{$j}")
                        ->setParameter("val{$i}", $filterValues['min'])
                        ->setParameter("val{$j}", $filterValues['max']);
                    $i++;
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
        }

        return $qb->getQuery()->getResult(AbstractQuery::HYDRATE_SCALAR_COLUMN);
    }

    public function getPaginatedValues(string $query, array $catInclude, array $catExclude, array $filters, int $page, int $maxPerPage = 12): Pagerfanta
    {
        $this->logger->info('here are filters');
        $this->logger->info(print_r($filters, true));

        if ($query === '' && empty($catInclude) && empty($catExclude) && empty($filters)) {
            return new Pagerfanta(new NullAdapter(0));
        }

        $qb = $this->createQueryBuilder('p')
            ->orderBy('p.id', 'ASC');

        if ($query !== '') {
            $qb
            ->andWhere('LOWER(p.name) LIKE :val')
            ->setParameter('val', strtolower('%' . $query . '%'));
        }

        if ($catInclude) {
            $qb
            ->andWhere('p.category IN (:val2)')
            ->setParameter('val2', $catInclude);
        }

        if ($catExclude) {
            $qb
            ->andWhere('p.category NOT IN (:val3)')
            ->setParameter('val3', $catExclude);
        }

        if ($filters) {
            $i = 4;
            $j = 5;

            foreach ($filters as $key => $filterValues) {
                if (!array_key_exists($key, $this->allowedFilters)) {
                    throw new Exception('Not allowed key value');
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


            // $separateConditions = [];
            // foreach ($filter as $key => $filterValue) {
            //     $targets = implode("', '", $filterValue);
            //     $condition = "p.{$key} IN ('" . $targets . "')";
            //     $separateConditions[] = $condition;
            // }

            // $fullConditionWithOr = implode(' OR ', $separateConditions);
            // $qb->andWhere($fullConditionWithOr);
        }


        $adapter = new QueryAdapter($qb);
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
