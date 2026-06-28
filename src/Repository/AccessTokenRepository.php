<?php

namespace App\Repository;

use App\Entity\AccessToken;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AccessToken>
 */
class AccessTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccessToken::class);
    }

    public function findOneByToken(string $token): ?AccessToken
    {
        // Soft-deleted (revoked) tokens must not authenticate.
        return $this->findOneBy(['token' => $token, 'deletedAt' => null]);
    }

    public function findOneByRefreshToken(string $refreshToken): ?AccessToken
    {
        return $this->findOneBy(['refreshToken' => $refreshToken, 'deletedAt' => null]);
    }

    /**
     * Soft-delete (revoke) every active token of a user except the given one.
     * Used on password change to log out other sessions while keeping the
     * caller's current session alive. Returns the number of tokens revoked.
     */
    public function revokeAllForUserExcept(User $user, ?int $exceptId): int
    {
        return $this->createQueryBuilder('t')
            ->update()
            ->set('t.deletedAt', ':now')
            ->andWhere('t.user = :user')
            ->andWhere('t.deletedAt IS NULL')
            ->andWhere('t.id != :exceptId')
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('user', $user)
            // -1 never matches a real id, so a null "current token" revokes all.
            ->setParameter('exceptId', $exceptId ?? -1)
            ->getQuery()
            ->execute();
    }
}
