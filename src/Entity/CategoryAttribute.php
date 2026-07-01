<?php

namespace App\Entity;

use App\Repository\CategoryAttributeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A spec field defined on a Category. Every product in the category is described
 * by its category's attributes (e.g. a sofa has "Oturacaq sayı", a bed has
 * "Çarpayı ölçüsü"). The `code` is a stable machine key, unique within the
 * category; `type` drives the input rendered in admin and how the value is
 * interpreted. `options` lists the allowed choices for select-type attributes.
 */
#[ORM\Entity(repositoryClass: CategoryAttributeRepository::class)]
#[ORM\Table(name: 'category_attributes')]
#[ORM\Index(name: 'idx_category_attribute_category', columns: ['category_id', 'sort_order'])]
#[ORM\UniqueConstraint(name: 'uniq_category_attribute_code', columns: ['category_id', 'code'])]
class CategoryAttribute
{
    public const TYPE_TEXT = 'text';
    public const TYPE_NUMBER = 'number';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_SELECT = 'select';

    public const TYPES = [self::TYPE_TEXT, self::TYPE_NUMBER, self::TYPE_BOOLEAN, self::TYPE_SELECT];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Category $category = null;

    #[ORM\Column(length: 120)]
    private ?string $label = null;

    #[ORM\Column(length: 80)]
    private ?string $code = null;

    #[ORM\Column(length: 20, options: ['default' => self::TYPE_TEXT])]
    private string $type = self::TYPE_TEXT;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $unit = null;

    /**
     * Allowed choices for select-type attributes; null/empty otherwise.
     *
     * @var list<string>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $options = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $required = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $filterable = false;

    #[ORM\Column(options: ['default' => 0])]
    private int $sortOrder = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(?string $unit): static
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getOptions(): array
    {
        return $this->options ?? [];
    }

    /**
     * @param list<string>|null $options
     */
    public function setOptions(?array $options): static
    {
        $this->options = $options ? array_values($options) : null;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): static
    {
        $this->required = $required;

        return $this;
    }

    public function isFilterable(): bool
    {
        return $this->filterable;
    }

    public function setFilterable(bool $filterable): static
    {
        $this->filterable = $filterable;

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
