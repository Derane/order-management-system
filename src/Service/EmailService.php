<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Order;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class EmailService implements EmailServiceInterface
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $fromEmail = 'noreply@example.com',
    ) {
    }

    public function sendWelcomeEmail(Order $order): void
    {
        $email = (new Email())
            ->from($this->fromEmail)
            ->to($order->getCustomerEmail())
            ->subject('Welcome! Your order has been received')
            ->html($this->buildWelcomeEmailContent($order));

        $this->mailer->send($email);
    }

    public function sendShippingEmail(Order $order): void
    {
        $email = (new Email())
            ->from($this->fromEmail)
            ->to($order->getCustomerEmail())
            ->subject('Your order has been shipped!')
            ->html($this->buildShippingEmailContent($order));

        $this->mailer->send($email);
    }

    public function sendThankYouEmail(Order $order): void
    {
        $email = (new Email())
            ->from($this->fromEmail)
            ->to($order->getCustomerEmail())
            ->subject('Thank you for your order!')
            ->html($this->buildThankYouEmailContent($order));

        $this->mailer->send($email);
    }

    private function buildWelcomeEmailContent(Order $order): string
    {
        $itemsHtml = '';
        foreach ($order->getItems() as $item) {
            $itemsHtml .= sprintf(
                '<li>%s - Quantity: %d - Price: $%s</li>',
                htmlspecialchars($item->getProductName()),
                $item->getQuantity(),
                number_format($item->getPrice() / 100, 2)
            );
        }

        return sprintf(
            '<h1>Welcome %s!</h1>
            <p>Thank you for your order #%d.</p>
            <h3>Order Details:</h3>
            <ul>%s</ul>
            <p><strong>Total amount: $%s</strong></p>
            <p>We will process your order shortly and keep you updated!</p>
            <p>Best regards,<br>Your Order Management Team</p>',
            htmlspecialchars($order->getCustomerName()),
            $order->getId(),
            $itemsHtml,
            $this->receiveTotalAmountString($order)
        );
    }

    private function buildShippingEmailContent(Order $order): string
    {
        return sprintf(
            '<h1>Good news %s!</h1>
            <p>Your order #%d has been shipped and is on its way!</p>
            <p><strong>Total amount: $%s</strong></p>
            <p>You should receive your order soon. We will notify you when it is delivered.</p>
            <p>Thank you for your business!</p>
            <p>Best regards,<br>Your Order Management Team</p>',
            htmlspecialchars($order->getCustomerName()),
            $order->getId(),
            $this->receiveTotalAmountString($order)
        );
    }

    private function buildThankYouEmailContent(Order $order): string
    {
        return sprintf(
            '<h1>Thank you %s!</h1>
            <p>Your order #%d has been successfully delivered!</p>
            <p><strong>Total amount: $%s</strong></p>
            <p>We hope you enjoy your purchase and we look forward to serving you again in the future.</p>
            <p>If you have any questions or concerns about your order, please do not hesitate to contact us.</p>
            <p>Best regards,<br>Your Order Management Team</p>',
            htmlspecialchars($order->getCustomerName()),
            $order->getId(),
            $this->receiveTotalAmountString($order)
        );
    }

    public function receiveTotalAmountString(Order $order): string
    {
        return number_format($order->getTotalAmount() / 100, 2);
    }
}
