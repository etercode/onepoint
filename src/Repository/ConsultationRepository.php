<?php

namespace App\Repository;

use App\Entity\Consultation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Consultation>
 */
class ConsultationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Consultation::class);
    }

    /**
     * @return list<Consultation>
     */
    public function findByFilters(?string $status, ?int $limit, int $offset): array
    {
        $qb = $this->filtered($status)
            ->orderBy('c.createdAt', 'DESC')
            ->addOrderBy('c.id', 'DESC');

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }
        if ($offset > 0) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    public function countByFilters(?string $status): int
    {
        return (int) $this->filtered($status)
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findOneActiveById(int $id): ?Consultation
    {
        return $this->findOneBy(['id' => $id, 'deletedAt' => null]);
    }

    private function filtered(?string $status): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c')->andWhere('c.deletedAt IS NULL');

        if (null !== $status) {
            $qb->andWhere('c.status = :status')->setParameter('status', $status);
        }

        return $qb;
    }
}
