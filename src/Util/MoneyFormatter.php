<?php

declare(strict_types=1);

namespace App\Util;

final class MoneyFormatter implements MoneyFormatterInterface
{
    public function floatToCents(float $amount): int
    {
        return (int) round($amount * 100, 0, \PHP_ROUND_HALF_UP);
    }

    public function centsToDecimalString(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
