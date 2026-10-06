<?php

declare(strict_types=1);

namespace App\Exceptions\Domain\Order;

use App\Exceptions\Domain\DomainException;

class InsufficientPaymentException extends DomainException
{
    public static function forTotal(int $totalCents, int $amountReceivedCents): self
    {
        $message = sprintf(
            'O valor recebido (%s) é menor que o total da venda (%s).',
            self::formatCents($amountReceivedCents),
            self::formatCents($totalCents),
        );

        return new self($message, ['amount_received_cents' => [$message]]);
    }

    private static function formatCents(int $cents): string
    {
        return 'R$ '.number_format($cents / 100, 2, ',', '.');
    }
}
