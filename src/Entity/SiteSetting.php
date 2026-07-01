<?php

namespace App\Entity;

use App\Repository\SiteSettingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A single editable site-configuration value, addressed by a stable string key
 * (e.g. "catalog_menu_heading"). Values are stored as text; callers interpret
 * them. Used for admin-editable storefront content that isn't worth a dedicated
 * table.
 */
#[ORM\Entity(repositoryClass: SiteSettingRepository::class)]
#[ORM\Table(name: 'site_settings')]
class SiteSetting
{
    #[ORM\Id]
    #[ORM\Column(name: 'setting_key', length: 100)]
    private string $key;

    #[ORM\Column(name: 'setting_value', type: Types::TEXT, nullable: true)]
    private ?string $value = null;

    public function __construct(string $key = '')
    {
        $this->key = $key;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): static
    {
        $this->key = $key;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): static
    {
        $this->value = $value;

        return $this;
    }
}
