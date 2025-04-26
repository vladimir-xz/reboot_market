<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Doctrine\ORM\AbstractQuery;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    private array $allowedFilters;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
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

    public function getAllWithChildrenAndParents()
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.children', 'cc')
            ->leftJoin('c.parent', 'cp')
            ->select('c', 'cc', 'cp')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // public function getRawTree(): array
    // {
    //     $conn = $this->getEntityManager()->getConnection();
    //     $sql = 'SELECT * FROM category';
    //     $stmt = $conn->prepare($sql);
    //     $result = $stmt->executeQuery();
    //     return $result->fetchAllAssociativeIndexed();
    // }

    public function getCategoriesFromSearch(string $query = '', array $catInclude = [], array $catExclude = [], array $filters = []): array
    {
        if ($query === '' && empty($catInclude) && empty($catInclude) && empty($filters)) {
            return [];
        }

        $qb = $this->createQueryBuilder('c', 'c.id')
            ->from('App\Entity\Product', 'p')
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
            $qb->leftJoin('p.specifications', 's')->select('s');
            $i = 4;
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
                    $qb->andWhere("s.{$key} IN (:val{$i})")
                        ->setParameter("val{$i}", $filterValues);
                }
                $i++;
            }
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
