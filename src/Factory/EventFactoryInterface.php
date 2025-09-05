<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Order;
use App\Entity\OrderStatus;
use App\Event\OrderCreatedEvent;
use App\Event\OrderStatusChangedEvent;

interface EventFactoryInterface
{
    public function createOrderCreatedEvent(Order $order): OrderCreatedEvent;

    public function createOrderStatusChangedEvent(
        Order $order,
        OrderStatus $fromStatus,
        OrderStatus $toStatus
    ): OrderStatusChangedEvent;
}
