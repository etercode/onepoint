<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use App\Repository\OrderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A customer order. There is no checkout flow yet, so orders are seeded /
 * admin-managed. `total` and `itemCount` are denormalised snapshots so the
 * admin list does not need to join line items. Line items live in OrderItem.
 *
 * Customer is stored as a name/email snapshot (guest-friendly); admins view
 * "customers" as an aggregation over these.
 */
#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'uniq_order_number', columns: ['order_number'], options: ['where' => '(deleted_at IS NULL)'])]
class Order
{
    use TimestampableTrait;

    public const STATUSES = ['pending', 'confirmed', 'delivering', 'completed', 'cancelled'];

    /** Statuses that count toward realised revenue. */
    public const REVENUE_STATUSES = ['delivering', 'completed'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    private ?string $orderNumber = null;

    #[ORM\Column(length: 180)]
    private ?string $customerName = null;

    #[ORM\Column(length: 180)]
    private ?string $customerEmail = null;

    #[ORM\Column(length: 20, options: ['default' => 'pending'])]
    private string $status = 'pending';

    /** Order total in whole manat (snapshot). */
    #[ORM\Column]
    private int $total = 0;

    /** Sum of item quantities (snapshot). */
    #[ORM\Column]
    private int $itemCount = 0;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $placedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string $orderNumber): static
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function setCustomerName(string $customerName): static
    {
        $this->customerName = $customerName;

        return $this;
    }

    public function getCustomerEmail(): ?string
    {
        return $this->customerEmail;
    }

    public function setCustomerEmail(string $customerEmail): static
    {
        $this->customerEmail = $customerEmail;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): static
    {
        $this->total = $total;

        return $this;
    }

    public function getItemCount(): int
    {
        return $this->itemCount;
    }

    public function setItemCount(int $itemCount): static
    {
        $this->itemCount = $itemCount;

        return $this;
    }

    public function getPlacedAt(): ?\DateTimeImmutable
    {
        return $this->placedAt;
    }

    public function setPlacedAt(\DateTimeImmutable $placedAt): static
    {
        $this->placedAt = $placedAt;

        return $this;
    }
}
