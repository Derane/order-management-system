<?php

declare(strict_types=1);

namespace App\Transformer;

use App\DTO\ViewOrderItem;
use App\Entity\OrderItem;

interface OrderItemToViewTransformerInterface
{
    public function transform(OrderItem $orderItem): ViewOrderItem;
}
