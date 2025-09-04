<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateOrderItemRequest
{
    #[Groups(['order:write', 'order:read'])]
    #[Assert\NotBlank]
    public string $productName = '';

    #[Groups(['order:write', 'order:read'])]
    #[Assert\Positive(message: 'Quantity must be greater than 0')]
    public int $quantity = 1;

    #[Groups(['order:write', 'order:read'])]
    #[Assert\Positive(message: 'Price must be greater than 0')]
    public float $price = 0.0;

    public function __construct(
        string $productName = '',
        int $quantity = 1,
        float $price = 0.0
    ) {
        $this->productName = $productName;
        $this->quantity = $quantity;
        $this->price = $price;
    }
}
