<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentMethod: int
{
    case CASH = 0;
    case CREDIT_CARD = 1;
    case DEBIT_CARD = 2;
    case PIX = 3;

    public function label(): string
    {
        return match ($this) {
            PaymentMethod::CASH => 'Dinheiro',
            PaymentMethod::CREDIT_CARD => 'Cartão de crédito',
            PaymentMethod::DEBIT_CARD => 'Cartão de débito',
            PaymentMethod::PIX => 'PIX',
        };
    }

    /**
     * Only cash payments need the amount handed over by the customer.
     */
    public function requiresAmountReceived(): bool
    {
        return $this === self::CASH;
    }

    /**
     * Shape sent to the frontend.
     *
     * @return array{value: int, label: string, requires_amount_received: bool}
     */
    public function toOption(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label(),
            'requires_amount_received' => $this->requiresAmountReceived(),
        ];
    }

    /**
     * @return list<array{value: int, label: string, requires_amount_received: bool}>
     */
    public static function options(): array
    {
        return array_map(fn (self $method): array => $method->toOption(), self::cases());
    }
}
