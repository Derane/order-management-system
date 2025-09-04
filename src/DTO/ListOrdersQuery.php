<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\OrderStatus;
use Symfony\Component\Validator\Constraints as Assert;

final class ListOrdersQuery
{
    #[Assert\Range(min: 1, max: 1000)]
    public int $page = 1;

    #[Assert\Range(min: 1, max: 100)]
    public int $limit = 10;

    public ?OrderStatus $status = null;

    public ?\DateTimeImmutable $dateFrom = null;

    public ?\DateTimeImmutable $dateTo = null;

    public ?string $email = null;
}
