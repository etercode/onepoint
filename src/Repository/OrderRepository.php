<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * @return list<Order>
     */
    public function findByFilters(?string $status, ?string $q, ?int $limit, int $offset): array
    {
        $qb = $this->filtered($status, $q)
            ->orderBy('o.placedAt', 'DESC')
            ->addOrderBy('o.id', 'DESC');

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }
        if ($offset > 0) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    public function countByFilters(?string $status, ?string $q): int
    {
        return (int) $this->filtered($status, $q)
            ->select('COUNT(o.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return list<Order>
     */
    public function findRecent(int $limit = 5): array
    {
        return $this->findByFilters(null, null, $limit, 0);
    }

    public function countActive(): int
    {
        return $this->count(['deletedAt' => null]);
    }

    /**
     * Realised revenue: sum of totals for delivering/completed orders.
     */
    public function sumRevenue(): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COALESCE(SUM(o.total), 0)')
            ->andWhere('o.deletedAt IS NULL')
            ->andWhere('o.status IN (:statuses)')
            ->setParameter('statuses', Order::REVENUE_STATUSES)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findOneActiveById(int $id): ?Order
    {
        return $this->findOneBy(['id' => $id, 'deletedAt' => null]);
    }

    /**
     * Customers aggregated from orders, newest buyers first.
     *
     * @return list<array{name: string, email: string, orders: int, spent: int, joined: string}>
     */
    public function aggregateCustomers(): array
    {
        /** @var list<array{email: string, name: string, orders: string, spent: string, joined: string}> $rows */
        $rows = $this->createQueryBuilder('o')
            ->select(
                'o.customerEmail AS email',
                'MAX(o.customerName) AS name',
                'COUNT(o.id) AS orders',
                'COALESCE(SUM(o.total), 0) AS spent',
                'MIN(o.placedAt) AS joined',
            )
            ->andWhere('o.deletedAt IS NULL')
            ->groupBy('o.customerEmail')
            ->orderBy('joined', 'DESC')
            ->getQuery()
            ->getResult();

        return array_map(static fn (array $r): array => [
            'name' => $r['name'],
            'email' => $r['email'],
            'orders' => (int) $r['orders'],
            'spent' => (int) $r['spent'],
            'joined' => substr((string) $r['joined'], 0, 10),
        ], $rows);
    }

    private function filtered(?string $status, ?string $q): QueryBuilder
    {
        $qb = $this->createQueryBuilder('o')->andWhere('o.deletedAt IS NULL');

        if (null !== $status) {
            $qb->andWhere('o.status = :status')->setParameter('status', $status);
        }
        if (null !== $q && '' !== trim($q)) {
            $qb->andWhere('LOWER(o.orderNumber) LIKE :q OR LOWER(o.customerName) LIKE :q OR LOWER(o.customerEmail) LIKE :q')
                ->setParameter('q', '%'.mb_strtolower(trim($q)).'%');
        }

        return $qb;
    }
}
