<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\OrderStatus;
use App\Message\OrderCreatedMessage;
use App\Message\OrderStatusChangedMessage;
use App\Repository\OrderRepository;
use App\Service\EmailServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

final class EmailNotificationHandler
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly EmailServiceInterface $emailService,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[AsMessageHandler]
    public function handleOrderCreated(OrderCreatedMessage $message): void
    {
        $order = $this->orderRepository->find($message->getOrderId());
        if (!$order) {
            $this->logger->warning(
                'Order not found for welcome email',
                ['orderId' => $message->getOrderId()]
            );
            return;
        }

        $this->emailService->sendWelcomeEmail($order);

        $this->logger->info(
            'Welcome email sent successfully',
            [
                'orderId' => $order->getId(),
                'customerEmail' => $order->getCustomerEmail(),
            ]
        );
    }

    #[AsMessageHandler]
    public function handleOrderStatusChanged(
        OrderStatusChangedMessage $message
    ): void {
        $order = $this->orderRepository->find($message->getOrderId());
        if (!$order) {
            $this->logger->warning(
                'Order not found for status change email',
                ['orderId' => $message->getOrderId()]
            );
            return;
        }

        match ($message->getNewStatus()) {
            OrderStatus::SHIPPED => $this->emailService->sendShippingEmail($order),
            OrderStatus::DELIVERED => $this->emailService->sendThankYouEmail($order),
            default => null,
        };

        if ($message->getNewStatus() === OrderStatus::SHIPPED ||
            $message->getNewStatus() === OrderStatus::DELIVERED) {
            $this->logger->info(
                'Status change email sent successfully',
                [
                    'orderId' => $order->getId(),
                    'oldStatus' => $message->getOldStatus()->value,
                    'newStatus' => $message->getNewStatus()->value,
                    'customerEmail' => $order->getCustomerEmail(),
                ]
            );
        }
    }
}
