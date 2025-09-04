<?php

declare(strict_types=1);

namespace App\Util;

interface MoneyFormatterInterface
{
    public function floatToCents(float $amount): int;

    public function centsToDecimalString(int $cents): string;
}
