<?php

namespace App\Repository;

use App\Dto\ProductQuery;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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
     * Active products matching the filters, with category and collection
     * fetch-joined so serialization does not trigger extra queries.
     *
     * @return list<Product>
     */
    public function findByFilters(ProductQuery $query): array
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('c', 'col')
            ->innerJoin('p.category', 'c')
            ->innerJoin('p.collection', 'col');

        $this->applyFilters($qb, $query);
        $this->applySort($qb, $query->sort);

        if (null !== $query->limit) {
            $qb->setMaxResults($query->limit);
        }
        if ($query->offset > 0) {
            $qb->setFirstResult($query->offset);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Total active products matching the filters, ignoring limit/offset.
     */
    public function countByFilters(ProductQuery $query): int
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(DISTINCT p.id)')
            ->innerJoin('p.category', 'c')
            ->innerJoin('p.collection', 'col');

        $this->applyFilters($qb, $query);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Products related to the given one (same collection or category), excluding
     * itself. Newest first.
     *
     * @return list<Product>
     */
    public function findRelated(Product $product, int $limit = 4): array
    {
        return $this->createQueryBuilder('p')
            ->addSelect('c', 'col')
            ->innerJoin('p.category', 'c')
            ->innerJoin('p.collection', 'col')
            ->andWhere('p.deletedAt IS NULL')
            ->andWhere('p.id != :id')
            ->andWhere('p.collection = :collection OR p.category = :category')
            ->setParameter('id', $product->getId())
            ->setParameter('collection', $product->getCollection())
            ->setParameter('category', $product->getCategory())
            ->orderBy('p.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findOneActiveById(int $id): ?Product
    {
        return $this->createQueryBuilder('p')
            ->addSelect('c', 'col')
            ->innerJoin('p.category', 'c')
            ->innerJoin('p.collection', 'col')
            ->andWhere('p.id = :id')
            ->andWhere('p.deletedAt IS NULL')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function applyFilters(QueryBuilder $qb, ProductQuery $query): void
    {
        $qb->andWhere('p.deletedAt IS NULL');

        if (null !== $query->ids) {
            $ids = array_values(array_filter(
                array_map('intval', explode(',', $query->ids)),
                static fn (int $id): bool => $id > 0,
            ));
            // No valid ids -> match nothing rather than everything.
            $qb->andWhere('p.id IN (:ids)')->setParameter('ids', [] === $ids ? [0] : $ids);
        }
        if (null !== $query->category) {
            $qb->andWhere('c.slug = :categorySlug')->setParameter('categorySlug', $query->category);
        }
        if (null !== $query->collection) {
            $qb->andWhere('col.slug = :collectionSlug')->setParameter('collectionSlug', $query->collection);
        }
        if (null !== $query->isNew) {
            $qb->andWhere('p.isNew = :isNew')->setParameter('isNew', $query->isNew);
        }
        if (null !== $query->onSale) {
            $qb->andWhere('p.onSale = :onSale')->setParameter('onSale', $query->onSale);
        }
        if (null !== $query->inStock) {
            $qb->andWhere('p.inStock = :inStock')->setParameter('inStock', $query->inStock);
        }
        if (null !== $query->minPrice) {
            $qb->andWhere('p.price >= :minPrice')->setParameter('minPrice', $query->minPrice);
        }
        if (null !== $query->maxPrice) {
            $qb->andWhere('p.price <= :maxPrice')->setParameter('maxPrice', $query->maxPrice);
        }
        if (null !== $query->q && '' !== trim($query->q)) {
            $qb->andWhere(
                'LOWER(p.name) LIKE :q OR LOWER(p.description) LIKE :q OR LOWER(p.material) LIKE :q '
                .'OR LOWER(c.name) LIKE :q OR LOWER(col.name) LIKE :q'
            )->setParameter('q', '%'.mb_strtolower(trim($query->q)).'%');
        }
    }

    private function applySort(QueryBuilder $qb, string $sort): void
    {
        match ($sort) {
            'price_asc' => $qb->orderBy('p.price', 'ASC'),
            'price_desc' => $qb->orderBy('p.price', 'DESC'),
            'newest' => $qb->orderBy('p.createdAt', 'DESC')->addOrderBy('p.id', 'DESC'),
            'name' => $qb->orderBy('p.name', 'ASC'),
            default => $qb->orderBy('p.id', 'ASC'),
        };
    }
}
