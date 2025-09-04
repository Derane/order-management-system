<?php

declare(strict_types=1);

namespace App\Transformer;

use App\DTO\CreateOrderItemRequest;
use App\Entity\OrderItem;

interface CreateOrderItemRequestToEntityTransformerInterface
{
    public function transform(CreateOrderItemRequest $request): OrderItem;
}
