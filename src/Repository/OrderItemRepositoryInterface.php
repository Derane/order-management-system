<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\OrderItem;

interface OrderItemRepositoryInterface
{
    public function save(OrderItem $orderItem): void;

    public function remove(OrderItem $orderItem): void;
}
