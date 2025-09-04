<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Serializer\Annotation\Groups;

final class ViewOrderItem
{
    #[Groups(['order:read'])]
    public string $productName;

    #[Groups(['order:read'])]
    public int $quantity;

    #[Groups(['order:read'])]
    public string $price;

    public function __construct(string $productName, int $quantity, string $price)
    {
        $this->productName = $productName;
        $this->quantity = $quantity;
        $this->price = $price;
    }
}
