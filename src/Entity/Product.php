<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A catalog product. Prices are whole manat (integers), matching the storefront
 * data. Each product belongs to one Category and one Collection.
 */
#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'products')]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'uniq_product_slug', columns: ['slug'], options: ['where' => '(deleted_at IS NULL)'])]
class Product
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $name = null;

    #[ORM\Column(length: 200)]
    private ?string $slug = null;

    /**
     * Current price in whole manat.
     */
    #[ORM\Column]
    private ?int $price = null;

    /**
     * Pre-discount price in whole manat; null when the product is not on sale.
     */
    #[ORM\Column(nullable: true)]
    private ?int $oldPrice = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $onSale = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $isNew = false;

    #[ORM\Column(options: ['default' => true])]
    private bool $inStock = true;

    #[ORM\Column(options: ['default' => true])]
    private bool $freeDelivery = true;

    #[ORM\Column(options: ['default' => 2])]
    private int $warrantyYears = 2;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Collection $collection = null;

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

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getOldPrice(): ?int
    {
        return $this->oldPrice;
    }

    public function setOldPrice(?int $oldPrice): static
    {
        $this->oldPrice = $oldPrice;

        return $this;
    }

    public function isOnSale(): bool
    {
        return $this->onSale;
    }

    public function setOnSale(bool $onSale): static
    {
        $this->onSale = $onSale;

        return $this;
    }

    public function isNew(): bool
    {
        return $this->isNew;
    }

    public function setIsNew(bool $isNew): static
    {
        $this->isNew = $isNew;

        return $this;
    }

    public function isInStock(): bool
    {
        return $this->inStock;
    }

    public function setInStock(bool $inStock): static
    {
        $this->inStock = $inStock;

        return $this;
    }

    public function isFreeDelivery(): bool
    {
        return $this->freeDelivery;
    }

    public function setFreeDelivery(bool $freeDelivery): static
    {
        $this->freeDelivery = $freeDelivery;

        return $this;
    }

    public function getWarrantyYears(): int
    {
        return $this->warrantyYears;
    }

    public function setWarrantyYears(int $warrantyYears): static
    {
        $this->warrantyYears = $warrantyYears;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getCollection(): ?Collection
    {
        return $this->collection;
    }

    public function setCollection(?Collection $collection): static
    {
        $this->collection = $collection;

        return $this;
    }
}
