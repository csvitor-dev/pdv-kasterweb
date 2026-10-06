<?php

declare(strict_types=1);

namespace App\Services\Order\Inputs;

use App\Enums\PaymentMethod;

/**
 * Only what the client is allowed to inform; prices and totals are always computed by the service.
 */
final readonly class CreateOrderInput
{
    /**
     * @param  list<OrderItemInput>  $items
     */
    public function __construct(
        public int $userId,
        public array $items,
        public PaymentMethod $paymentMethod,
        public ?int $amountReceivedCents,
    ) {}
}
