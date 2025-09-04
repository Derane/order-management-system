<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\OrderStatus;
use Symfony\Component\Validator\Constraints as Assert;

final class UpdateOrderStatusRequest
{
    #[Assert\NotNull(message: 'Status is required')]
    public OrderStatus $status;
}
