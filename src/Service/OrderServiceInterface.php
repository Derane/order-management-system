<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CreateOrderRequest;
use App\Entity\Order;
use App\Entity\OrderStatus;

interface OrderServiceInterface
{
    public function createOrder(CreateOrderRequest $request): Order;

    public function updateOrder(Order $order, CreateOrderRequest $request): Order;

    public function updateOrderStatus(Order $order, OrderStatus $status): Order;

    public function deleteOrder(Order $order): void;
}
