<?php

namespace App\Entity;

use App\Repository\ProductAttributeValueRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * A product's value for one of its category's attributes. Values are stored as
 * strings regardless of the attribute type (booleans as "1"/"0", numbers as
 * their decimal string); the attribute's `type` governs interpretation and
 * display. There is at most one value per (product, attribute).
 */
#[ORM\Entity(repositoryClass: ProductAttributeValueRepository::class)]
#[ORM\Table(name: 'product_attribute_values')]
#[ORM\Index(name: 'idx_product_attribute_value_product', columns: ['product_id'])]
#[ORM\UniqueConstraint(name: 'uniq_product_attribute_value', columns: ['product_id', 'attribute_id'])]
class ProductAttributeValue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Product $product = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?CategoryAttribute $attribute = null;

    #[ORM\Column(length: 500)]
    private ?string $value = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getAttribute(): ?CategoryAttribute
    {
        return $this->attribute;
    }

    public function setAttribute(CategoryAttribute $attribute): static
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }
}
