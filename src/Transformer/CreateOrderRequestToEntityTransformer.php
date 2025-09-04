<?php

declare(strict_types=1);

namespace App\Transformer;

use App\DTO\CreateOrderRequest;
use App\Entity\Order;
use App\Util\MoneyFormatterInterface;

final class CreateOrderRequestToEntityTransformer implements
    CreateOrderRequestToEntityTransformerInterface
{
    public function __construct(
        private readonly MoneyFormatterInterface $moneyFormatter,
        private readonly CreateOrderItemRequestToEntityTransformerInterface $itemTransformer,
    ) {
    }

    public function transform(CreateOrderRequest $request): Order
    {
        $order = new Order();
        $order->setCustomerName($request->customerName);
        $order->setCustomerEmail($request->customerEmail);

        $this->addItemsToOrder($order, $request->items);
        $this->calculateAndSetTotalAmount($order);

        return $order;
    }

    private function addItemsToOrder(Order $order, array $items): void
    {
        foreach ($items as $itemData) {
            // Convert array to CreateOrderItemRequest if needed
            if (is_array($itemData)) {
                $itemRequest = new \App\DTO\CreateOrderItemRequest(
                    $itemData['productName'] ?? '',
                    (int) ($itemData['quantity'] ?? 1),
                    (float) ($itemData['price'] ?? 0.0)
                );
            } else {
                $itemRequest = $itemData;
            }

            $orderItem = $this->itemTransformer->transform($itemRequest);
            $order->addItem($orderItem);
        }
    }

    private function calculateAndSetTotalAmount(Order $order): void
    {
        $totalCents = 0;

        foreach ($order->getItems() as $item) {
            $itemCents = $this->moneyFormatter->floatToCents(
                (float) $item->getPrice()
            );
            $totalCents += $itemCents * $item->getQuantity();
        }

        $totalAmount = $this->moneyFormatter->centsToDecimalString(
            $totalCents
        );
        $order->setTotalAmount($totalAmount);
    }
}
