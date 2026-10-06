<?php

declare(strict_types=1);

namespace App\Services\Order\Inputs;

final readonly class OrderItemInput
{
    public function __construct(
        public string $productUuid,
        public int $quantity,
    ) {}
}
