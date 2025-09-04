<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\OrderCreatedEvent;
use App\Event\OrderStatusChangedEvent;
use App\Message\OrderCreatedMessage;
use App\Message\OrderStatusChangedMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class OrderEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function onOrderCreated(OrderCreatedEvent $event): void
    {
        $order = $event->getOrder();

        $this->messageBus->dispatch(
            new OrderCreatedMessage($order->getId())
        );
    }

    public function onOrderStatusChanged(OrderStatusChangedEvent $event): void
    {
        $order = $event->getOrder();

        $this->messageBus->dispatch(
            new OrderStatusChangedMessage(
                $order->getId(),
                $event->getOldStatus(),
                $event->getNewStatus()
            )
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OrderCreatedEvent::class => 'onOrderCreated',
            OrderStatusChangedEvent::class => 'onOrderStatusChanged',
        ];
    }
}
