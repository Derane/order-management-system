<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Order;

final class OrderCreatedEvent
{
    public function __construct(private readonly Order $order)
    {
    }

    public function getOrder(): Order
    {
        return $this->order;
    }
}
