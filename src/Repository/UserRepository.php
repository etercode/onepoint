<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Whether a non-soft-deleted user already uses this email.
     * Uses COUNT so we don't hydrate an entity just to test existence.
     */
    public function existsActiveByEmail(string $email): bool
    {
        return $this->count(['email' => $email, 'deletedAt' => null]) > 0;
    }

    /**
     * Whether a non-soft-deleted user already uses this username.
     */
    public function existsActiveByUsername(string $username): bool
    {
        return $this->count(['username' => $username, 'deletedAt' => null]) > 0;
    }
}
