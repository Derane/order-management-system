<?php

declare(strict_types=1);

namespace App\Transformer;

use App\DTO\CreateOrderRequest;
use App\Entity\Order;

interface CreateOrderRequestToEntityTransformerInterface
{
    public function transform(CreateOrderRequest $request): Order;
}
