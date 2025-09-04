<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Order;

interface EmailServiceInterface
{
    public function sendWelcomeEmail(Order $order): void;

    public function sendShippingEmail(Order $order): void;

    public function sendThankYouEmail(Order $order): void;
}
