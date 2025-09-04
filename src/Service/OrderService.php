<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CreateOrderRequest;
use App\Entity\Order;
use App\Entity\OrderStatus;
use App\Event\OrderCreatedEvent;
use App\Event\OrderStatusChangedEvent;
use App\Repository\OrderRepository;
use App\Transformer\CreateOrderRequestToEntityTransformerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class OrderService implements OrderServiceInterface
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly CreateOrderRequestToEntityTransformerInterface $transformer,
        private readonly ValidatorInterface $validator,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function createOrder(CreateOrderRequest $request): Order
    {
        $this->validateRequest($request);

        $order = $this->transformer->transform($request);
        $this->validateEntity($order);

        $this->orderRepository->save($order);

        $this->eventDispatcher->dispatch(
            new OrderCreatedEvent($order)
        );

        return $order;
    }

    public function updateOrder(
        Order $order,
        CreateOrderRequest $request
    ): Order {
        $this->validateRequest($request);

        $originalStatus = $order->getStatus();

        $order->setCustomerName($request->customerName);
        $order->setCustomerEmail($request->customerEmail);

        $order->getItems()->clear();

        $newOrder = $this->transformer->transform($request);
        foreach ($newOrder->getItems() as $item) {
            $order->addItem($item);
        }

        $this->recalculateTotalAmount($order);
        $this->validateEntity($order);

        $this->orderRepository->save($order);

        if ($originalStatus !== $order->getStatus()) {
            $this->eventDispatcher->dispatch(
                new OrderStatusChangedEvent(
                    $order,
                    $originalStatus,
                    $order->getStatus()
                ),
                OrderStatusChangedEvent::class
            );
        }

        return $order;
    }

    public function updateOrderStatus(
        Order $order,
        OrderStatus $status
    ): Order {
        $originalStatus = $order->getStatus();

        if ($originalStatus === $status) {
            return $order;
        }

        $order->setStatus($status);
        $this->validateEntity($order);

        $this->orderRepository->save($order);

        $this->eventDispatcher->dispatch(
            new OrderStatusChangedEvent(
                $order,
                $originalStatus,
                $status
            ),
            OrderStatusChangedEvent::class
        );

        return $order;
    }

    public function deleteOrder(Order $order): void
    {
        $this->orderRepository->remove($order);
    }

    private function validateRequest(CreateOrderRequest $request): void
    {
        $violations = $this->validator->validate($request);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            throw new \InvalidArgumentException(implode(', ', $errors));
        }
    }

    private function validateEntity(Order $order): void
    {
        $violations = $this->validator->validate($order);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            throw new \InvalidArgumentException(implode(', ', $errors));
        }
    }

    private function recalculateTotalAmount(Order $order): void
    {
        $total = 0.0;

        foreach ($order->getItems() as $item) {
            $total += (float) $item->getPrice() * $item->getQuantity();
        }

        $order->setTotalAmount(number_format($total, 2, '.', ''));
    }
}
