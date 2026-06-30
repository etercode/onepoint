<?php

namespace App\Dto;

use App\Entity\Order;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Admin payload to change an order's status.
 */
class OrderStatusRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Choice(choices: Order::STATUSES)]
        public string $status = '',
    ) {
    }
}
