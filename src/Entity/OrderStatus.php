<?php

declare(strict_types=1);

namespace App\Entity;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public static function default(): self
    {
        return self::PENDING;
    }
}
