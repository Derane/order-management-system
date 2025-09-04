<?php

declare(strict_types=1);

namespace App\Transformer;

use App\DTO\ViewOrder;
use App\DTO\ViewOrderItem;
use App\Entity\Order;

final class OrderToViewTransformer implements OrderToViewTransformerInterface
{
    public function __construct(
        private readonly OrderItemToViewTransformerInterface $itemTransformer,
    ) {
    }

    public function transform(Order $order): ViewOrder
    {
        return new ViewOrder(
            id: $order->getId() ?? 0,
            customerName: $order->getCustomerName(),
            customer_email: $order->getCustomerEmail(),
            totalAmount: $order->getTotalAmount(),
            status: $order->getStatus()->value,
            createdAt: $order->getCreatedAt(),
            updatedAt: $order->getUpdatedAt(),
            items: $this->transformItems($order),
        );
    }

    /** @return ViewOrderItem[] */
    private function transformItems(Order $order): array
    {
        $viewItems = [];

        foreach ($order->getItems() as $item) {
            $viewItems[] = $this->itemTransformer->transform($item);
        }

        return $viewItems;
    }
}
