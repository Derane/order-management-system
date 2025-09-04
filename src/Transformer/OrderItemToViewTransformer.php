<?php

declare(strict_types=1);

namespace App\Transformer;

use App\DTO\ViewOrderItem;
use App\Entity\OrderItem;

final class OrderItemToViewTransformer implements
    OrderItemToViewTransformerInterface
{
    public function transform(OrderItem $orderItem): ViewOrderItem
    {
        return new ViewOrderItem(
            productName: $orderItem->getProductName(),
            quantity: $orderItem->getQuantity(),
            price: $orderItem->getPrice(),
        );
    }
}
