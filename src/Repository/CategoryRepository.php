<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * All active categories with their live product count and lowest price.
     * Categories with no products are still returned (count 0, priceFrom null).
     *
     * @return list<array{category: Category, productCount: int, priceFrom: int|null}>
     */
    public function findAllWithStats(): array
    {
        $rows = $this->statsQuery()
            ->orderBy('c.sortOrder', 'ASC')
            ->addOrderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map($this->mapRow(...), $rows);
    }

    /**
     * One active category by slug with its product count and lowest price.
     *
     * @return array{category: Category, productCount: int, priceFrom: int|null}|null
     */
    public function findOneBySlugWithStats(string $slug): ?array
    {
        $row = $this->statsQuery()
            ->andWhere('c.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();

        return null === $row ? null : $this->mapRow($row);
    }

    public function findOneActiveById(int $id): ?Category
    {
        return $this->findOneBy(['id' => $id, 'deletedAt' => null]);
    }

    /**
     * Whether an active category (other than $excludeId) already uses this slug.
     */
    public function existsActiveBySlugExcludingId(string $slug, ?int $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.slug = :slug')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('slug', $slug);

        if (null !== $excludeId) {
            $qb->andWhere('c.id != :id')->setParameter('id', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    private function statsQuery(): \Doctrine\ORM\QueryBuilder
    {
        return $this->createQueryBuilder('c')
            ->select('c', 'COUNT(p.id) AS productCount', 'MIN(p.price) AS priceFrom')
            ->leftJoin(Product::class, 'p', 'WITH', 'p.category = c AND p.deletedAt IS NULL')
            ->andWhere('c.deletedAt IS NULL')
            ->groupBy('c.id');
    }

    /**
     * Maps a mixed entity/scalar result row (entity at index 0) to a typed shape.
     *
     * @param array{0: Category, productCount: string, priceFrom: string|null} $row
     *
     * @return array{category: Category, productCount: int, priceFrom: int|null}
     */
    private function mapRow(array $row): array
    {
        return [
            'category' => $row[0],
            'productCount' => (int) $row['productCount'],
            'priceFrom' => null === $row['priceFrom'] ? null : (int) $row['priceFrom'],
        ];
    }
}
