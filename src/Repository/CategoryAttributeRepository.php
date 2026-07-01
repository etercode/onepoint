<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\CategoryAttribute;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CategoryAttribute>
 */
class CategoryAttributeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryAttribute::class);
    }

    /**
     * @return list<CategoryAttribute>
     */
    public function findForCategory(Category $category): array
    {
        return $this->findBy(['category' => $category], ['sortOrder' => 'ASC', 'id' => 'ASC']);
    }

    public function findOneByCategoryAndCode(Category $category, string $code): ?CategoryAttribute
    {
        return $this->findOneBy(['category' => $category, 'code' => $code]);
    }

    public function existsByCategoryAndCode(Category $category, string $code, ?int $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.category = :category')
            ->andWhere('a.code = :code')
            ->setParameter('category', $category)
            ->setParameter('code', $code);

        if (null !== $excludeId) {
            $qb->andWhere('a.id != :excludeId')->setParameter('excludeId', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function maxSortOrder(Category $category): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COALESCE(MAX(a.sortOrder), -1)')
            ->andWhere('a.category = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
