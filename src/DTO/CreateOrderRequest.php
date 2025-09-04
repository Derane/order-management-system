<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateOrderRequest
{
    #[Groups(['order:write', 'order:read'])]
    #[Assert\NotBlank]
    public string $customerName = '';

    #[Groups(['order:write', 'order:read'])]
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $customerEmail = '';

    /** @var CreateOrderItemRequest[] */
    #[Groups(['order:write', 'order:read'])]
    #[Assert\Count(min: 1, minMessage: 'At least one item is required')]
    #[Assert\Valid]
    public array $items = [];

    public function __construct(
        string $customerName = '',
        string $customerEmail = '',
        array $items = []
    ) {
        $this->customerName = $customerName;
        $this->customerEmail = $customerEmail;
        $this->items = $items;
    }
}
