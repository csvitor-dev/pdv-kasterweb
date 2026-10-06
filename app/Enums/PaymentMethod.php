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
}
