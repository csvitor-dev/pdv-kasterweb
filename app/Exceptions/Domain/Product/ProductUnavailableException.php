<?php

declare(strict_types=1);

namespace App\Exceptions\Domain\Product;

use App\Exceptions\Domain\DomainException;

class ProductUnavailableException extends DomainException
{
    public static function notFound(int $itemIndex): self
    {
        return self::forItem($itemIndex, 'Produto não encontrado.');
    }

    public static function inactive(int $itemIndex, string $productName): self
    {
        return self::forItem($itemIndex, "O produto {$productName} não está mais disponível para venda.");
    }

    public static function insufficientStock(int $itemIndex, string $productName, int $availableStock): self
    {
        return self::forItem($itemIndex, "Estoque insuficiente para {$productName}: {$availableStock} disponível(is).");
    }

    private static function forItem(int $itemIndex, string $message): self
    {
        return new self($message, ["items.{$itemIndex}.product_uuid" => [$message]]);
    }
}
