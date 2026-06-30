<?php

namespace App\Sales;

use App\Entity\Consultation;
use App\Entity\Order;
use App\Entity\OrderItem;

/**
 * JSON shapes for orders, order items, consultations and aggregated customers.
 */
final class SalesPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function order(Order $order): array
    {
        return [
            'id' => $order->getId(),
            'orderNumber' => $order->getOrderNumber(),
            'customer' => $order->getCustomerName(),
            'email' => $order->getCustomerEmail(),
            'items' => $order->getItemCount(),
            'total' => $order->getTotal(),
            'status' => $order->getStatus(),
            'date' => $order->getPlacedAt()?->format('Y-m-d'),
        ];
    }

    /**
     * @param list<Order> $orders
     *
     * @return list<array<string, mixed>>
     */
    public function orders(array $orders): array
    {
        return array_map($this->order(...), $orders);
    }

    /**
     * Order with its line items, for the detail view.
     *
     * @param list<OrderItem> $items
     *
     * @return array<string, mixed>
     */
    public function orderDetail(Order $order, array $items): array
    {
        return $this->order($order) + [
            'lines' => array_map(static fn (OrderItem $i): array => [
                'productId' => $i->getProduct()?->getId(),
                'name' => $i->getProductName(),
                'unitPrice' => $i->getUnitPrice(),
                'quantity' => $i->getQuantity(),
                'lineTotal' => $i->getLineTotal(),
            ], $items),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function consultation(Consultation $consultation): array
    {
        return [
            'id' => $consultation->getId(),
            'name' => $consultation->getName(),
            'phone' => $consultation->getPhone(),
            'room' => $consultation->getRoom(),
            'message' => $consultation->getMessage(),
            'status' => $consultation->getStatus(),
            'createdAt' => $consultation->getCreatedAt()?->format(\DateTimeInterface::ATOM),
        ];
    }

    /**
     * @param list<Consultation> $consultations
     *
     * @return list<array<string, mixed>>
     */
    public function consultations(array $consultations): array
    {
        return array_map($this->consultation(...), $consultations);
    }
}
