<?php

declare(strict_types=1);

namespace App\Transformer;

use App\DTO\ViewOrder;
use App\Entity\Order;

interface OrderToViewTransformerInterface
{
    public function transform(Order $order): ViewOrder;
}
