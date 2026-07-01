<?php

namespace App\Repository;

use App\Entity\SiteSetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SiteSetting>
 */
class SiteSettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SiteSetting::class);
    }

    /**
     * All settings as a key => value map.
     *
     * @return array<string, string|null>
     */
    public function map(): array
    {
        $map = [];
        foreach ($this->findAll() as $setting) {
            $map[$setting->getKey()] = $setting->getValue();
        }

        return $map;
    }

    /**
     * Upsert a value for a key (does not flush).
     */
    public function set(string $key, ?string $value): void
    {
        $setting = $this->find($key) ?? new SiteSetting($key);
        $setting->setValue($value);
        $this->getEntityManager()->persist($setting);
    }
}
