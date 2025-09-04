<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Serializer\Annotation\Groups;

final class ViewOrder
{
    #[Groups(['order:read'])]
    public int $id;

    #[Groups(['order:read'])]
    public string $customerName;

    #[Groups(['order:read'])]
    public string $customer_email;

    #[Groups(['order:read'])]
    public string $totalAmount;

    #[Groups(['order:read'])]
    public string $status;

    #[Groups(['order:read'])]
    public \DateTimeImmutable $createdAt;

    #[Groups(['order:read'])]
    public \DateTimeImmutable $updatedAt;

    /** @var ViewOrderItem[] */
    #[Groups(['order:read'])]
    public array $items;

    public function __construct(
        int $id,
        string $customerName,
        string $customer_email,
        string $totalAmount,
        string $status,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        array $items
    ) {
        $this->id = $id;
        $this->customerName = $customerName;
        $this->customer_email = $customer_email;
        $this->totalAmount = $totalAmount;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->items = $items;
    }
}
