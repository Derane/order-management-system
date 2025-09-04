<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Order;
use App\Entity\OrderStatus;
use Symfony\Contracts\EventDispatcher\Event;

final class OrderStatusChangedEvent extends Event
{

    public function __construct(
        private readonly Order $order,
        private readonly OrderStatus $oldStatus,
        private readonly OrderStatus $newStatus,
    ) {
    }

    public function getOrder(): Order
    {
        return $this->order;
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
