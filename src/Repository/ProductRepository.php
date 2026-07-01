<?php

namespace App\Repository;

use App\Dto\ProductQuery;
use App\Entity\Category;
use App\Entity\Collection;
use App\Entity\Product;
use Doctrine\DBAL\ParameterType;
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

    /**
     * Whether an active product (other than $excludeId) already uses this slug.
     */
    public function existsActiveBySlugExcludingId(string $slug, ?int $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.slug = :slug')
            ->andWhere('p.deletedAt IS NULL')
            ->setParameter('slug', $slug);

        if (null !== $excludeId) {
            $qb->andWhere('p.id != :id')->setParameter('id', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function countActiveByCategory(Category $category): int
    {
        return $this->count(['category' => $category, 'deletedAt' => null]);
    }

    public function countActiveByCollection(Collection $collection): int
    {
        return $this->count(['collection' => $collection, 'deletedAt' => null]);
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
                'LOWER(p.name) LIKE :q OR LOWER(p.description) LIKE :q '
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

    /**
     * Random active products that have at least one image, for the catalog-menu
     * promo strip. Returns lightweight rows (not entities).
     *
     * @return list<array{id: int, name: string, slug: string, price: int, image: ?string}>
     */
    public function findRandomForPromo(int $limit): array
    {
        $sql = <<<'SQL'
            SELECT p.id, p.name, p.slug, p.price,
                   (SELECT pi.url FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.sort_order ASC, pi.id ASC LIMIT 1) AS image
            FROM products p
            WHERE p.deleted_at IS NULL
              AND EXISTS (SELECT 1 FROM product_images pi WHERE pi.product_id = p.id)
            ORDER BY RANDOM()
            LIMIT :limit
            SQL;

        /** @var list<array{id: int, name: string, slug: string, price: int, image: ?string}> */
        return $this->getEntityManager()->getConnection()
            ->executeQuery($sql, ['limit' => $limit], ['limit' => ParameterType::INTEGER])
            ->fetchAllAssociative();
    }

    /**
     * Typo-tolerant product search for the storefront autocomplete, using
     * pg_trgm. Ranks exact substring matches first, then by trigram similarity,
     * so misspellings ("divann", "lena divan") still surface results. Returns
     * lightweight rows plus whether each was a substring ("exact") match and its
     * similarity, so the caller can decide on a "did you mean" suggestion.
     *
     * @return list<array{id: int, name: string, slug: string, price: int, image: ?string, exact: bool, sim: float}>
     */
    public function searchSuggest(string $q, int $limit): array
    {
        $q = trim($q);
        if ('' === $q) {
            return [];
        }

        // word_similarity(q, name) compares the query against the most similar
        // word in the name, so short/partial typos ("lna" -> "Lena") and
        // multi-word names still match, unlike whole-string similarity().
        $sql = <<<'SQL'
            SELECT p.id, p.name, p.slug, p.price,
                   (SELECT pi.url FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.sort_order ASC, pi.id ASC LIMIT 1) AS image,
                   (LOWER(p.name) LIKE LOWER(:like)) AS exact,
                   GREATEST(similarity(p.name, :q), word_similarity(:q, p.name)) AS sim
            FROM products p
            WHERE p.deleted_at IS NULL
              AND (LOWER(p.name) LIKE LOWER(:like) OR word_similarity(:q, p.name) > 0.3)
            ORDER BY exact DESC, sim DESC, p.id ASC
            LIMIT :limit
            SQL;

        $rows = $this->getEntityManager()->getConnection()
            ->executeQuery(
                $sql,
                ['q' => $q, 'like' => '%'.$q.'%', 'limit' => $limit],
                ['limit' => ParameterType::INTEGER],
            )
            ->fetchAllAssociative();

        return array_map(static fn (array $r): array => [
            'id' => (int) $r['id'],
            'name' => (string) $r['name'],
            'slug' => (string) $r['slug'],
            'price' => (int) $r['price'],
            'image' => $r['image'],
            'exact' => (bool) $r['exact'],
            'sim' => (float) $r['sim'],
        ], $rows);
    }
}
