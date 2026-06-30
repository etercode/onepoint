<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\OrderItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderItem>
 */
class OrderItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderItem::class);
    }

    /**
     * @return list<OrderItem>
     */
    public function findByOrder(Order $order): array
    {
        return $this->findBy(['orderRef' => $order], ['id' => 'ASC']);
    }

    /**
     * Best-selling products by units sold (across non-deleted orders).
     *
     * @return list<array{productId: int|null, name: string, units: int, revenue: int}>
     */
    public function topProducts(int $limit = 5): array
    {
        /** @var list<array{productId: ?int, name: string, units: string, revenue: string}> $rows */
        $rows = $this->createQueryBuilder('i')
            ->select(
                'IDENTITY(i.product) AS productId',
                'MAX(i.productName) AS name',
                'SUM(i.quantity) AS units',
                'SUM(i.unitPrice * i.quantity) AS revenue',
            )
            ->innerJoin('i.orderRef', 'o')
            ->andWhere('o.deletedAt IS NULL')
            ->groupBy('i.product')
            ->orderBy('units', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return array_map(static fn (array $r): array => [
            'productId' => null === $r['productId'] ? null : (int) $r['productId'],
            'name' => $r['name'],
            'units' => (int) $r['units'],
            'revenue' => (int) $r['revenue'],
        ], $rows);
    }
}
