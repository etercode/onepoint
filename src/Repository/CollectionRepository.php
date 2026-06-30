<?php

namespace App\Repository;

use App\Entity\Collection;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Collection>
 */
class CollectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Collection::class);
    }

    /**
     * Active collections with their live product count.
     *
     * @return list<array{collection: Collection, productCount: int}>
     */
    public function findAllWithStats(bool $featuredOnly = false): array
    {
        $qb = $this->statsQuery()
            ->orderBy('c.sortOrder', 'ASC')
            ->addOrderBy('c.name', 'ASC');

        if ($featuredOnly) {
            $qb->andWhere('c.featured = true');
        }

        return array_map($this->mapRow(...), $qb->getQuery()->getResult());
    }

    /**
     * One active collection by slug with its product count.
     *
     * @return array{collection: Collection, productCount: int}|null
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

    public function findOneActiveByName(string $name): ?Collection
    {
        return $this->findOneBy(['name' => $name, 'deletedAt' => null]);
    }

    private function statsQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('c')
            ->select('c', 'COUNT(p.id) AS productCount')
            ->leftJoin(Product::class, 'p', 'WITH', 'p.collection = c AND p.deletedAt IS NULL')
            ->andWhere('c.deletedAt IS NULL')
            ->groupBy('c.id');
    }

    /**
     * @param array{0: Collection, productCount: string} $row
     *
     * @return array{collection: Collection, productCount: int}
     */
    private function mapRow(array $row): array
    {
        return [
            'collection' => $row[0],
            'productCount' => (int) $row['productCount'],
        ];
    }
}
