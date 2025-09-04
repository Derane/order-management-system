<?php

declare(strict_types=1);

namespace App\Transformer;

use App\DTO\CreateOrderItemRequest;
use App\Entity\OrderItem;
use App\Util\MoneyFormatterInterface;

final class CreateOrderItemRequestToEntityTransformer implements
    CreateOrderItemRequestToEntityTransformerInterface
{
    public function __construct(
        private readonly MoneyFormatterInterface $moneyFormatter,
    ) {
    }

    public function transform(CreateOrderItemRequest $request): OrderItem
    {
        $orderItem = new OrderItem();
        $orderItem->setProductName($request->productName);
        $orderItem->setQuantity($request->quantity);

        $priceAsDecimal = $this->formatPriceToDecimal($request->price);
        $orderItem->setPrice($priceAsDecimal);

        return $orderItem;
    }

    private function formatPriceToDecimal(float $price): string
    {
        $cents = $this->moneyFormatter->floatToCents($price);

        return $this->moneyFormatter->centsToDecimalString($cents);
    }
}
