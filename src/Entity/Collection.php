<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use App\Repository\CollectionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * A product collection / series (e.g. "Lena", "Borneo"). Every product belongs
 * to one collection. "featured" collections are the curated set shown on the
 * storefront home page. Product counts are derived at query time.
 */
#[ORM\Entity(repositoryClass: CollectionRepository::class)]
#[ORM\Table(name: 'collections')]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'uniq_collection_slug', columns: ['slug'], options: ['where' => '(deleted_at IS NULL)'])]
class Collection
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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tagline = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $image = null;

    /**
     * Whether this collection is part of the curated set shown on the home page.
     */
    #[ORM\Column(options: ['default' => false])]
    private bool $featured = false;

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

    public function getTagline(): ?string
    {
        return $this->tagline;
    }

    public function setTagline(?string $tagline): static
    {
        $this->tagline = $tagline;

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

    public function isFeatured(): bool
    {
        return $this->featured;
    }

    public function setFeatured(bool $featured): static
    {
        $this->featured = $featured;

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
