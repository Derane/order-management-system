<?php

declare(strict_types=1);

namespace App\Message;

use App\Entity\OrderStatus;

final class OrderStatusChangedMessage
{
    public function __construct(
        private readonly int $orderId,
        private readonly OrderStatus $oldStatus,
        private readonly OrderStatus $newStatus,
    ) {
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getOldStatus(): OrderStatus
    {
        return $this->oldStatus;
    }

    public function getNewStatus(): OrderStatus
    {
        return $this->newStatus;
    }
}
