<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Order;
use App\Entity\OrderStatus;
use Doctrine\ORM\Tools\Pagination\Paginator;

interface OrderRepositoryInterface
{
    public function findWithFilters(
        int $page = 1,
        int $limit = 10,
        ?OrderStatus $status = null,
        ?\DateTimeInterface $dateFrom = null,
        ?\DateTimeInterface $dateTo = null,
        ?string $email = null
    ): Paginator;

    public function save(Order $order): void;

    public function remove(Order $order): void;
}
