<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use App\Repository\CategoryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * A top-level catalog category (e.g. "Yumşaq mebel"). Products belong to exactly
 * one category. Product counts and "price from" are derived at query time, not
 * stored. Uniqueness is partial (only among non-soft-deleted rows).
 */
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: 'categories')]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'uniq_category_slug', columns: ['slug'], options: ['where' => '(deleted_at IS NULL)'])]
class Category
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private ?string $name = null;

    #[ORM\Column(length: 140)]
    private ?string $slug = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $image = null;

    /**
     * Manual ordering for display; lower comes first.
     */
    #[ORM\Column(options: ['default' => 0])]
    private int $sortOrder = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
}
