<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\AbstractQuery;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function getRawTree(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT * FROM category';
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();
        return $result->fetchAllAssociativeIndexed();
    }

    public function getCategoriesFromSearch(string $query = '', array $catInclude = [], array $catExclude = [], array $filter = []): array
    {
        // if ($query === '' && empty($catInclude) && empty($catInclude) && empty($filter)) {
        //     return [];
        // }

        $qb = $this->createQueryBuilder('c', 'c.id')
            ->join('c.products', 'p')
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

        if ($filter) {
            $separateConditions = [];
            foreach ($filter as $key => $filterValue) {
                $targets = implode("', '", $filterValue);
                $condition = "p.{$key} IN ('" . $targets . "')";
                $separateConditions[] = $condition;
            }

            $fullConditionWithOr = implode(' OR ', $separateConditions);
            $this->logger->info($fullConditionWithOr);
            $qb->andWhere($fullConditionWithOr);
        }

        return $qb->getQuery()->getResult();
    }

    // public function getRawTree(): array
    // {
    //     $entityManager = $this->getEntityManager();

    //     $query = $entityManager->createQuery(
    //         'SELECT c, ch
    //         FROM App\Entity\Category c
    //         INNER JOIN c.children ch'
    //     );

    //     return $query->getArrayResult();
    // }


    //    /**
    //     * @return Category[] Returns an array of Category objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Category
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
