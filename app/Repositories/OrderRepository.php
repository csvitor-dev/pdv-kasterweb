<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepository as OrderRepositoryContract;

class OrderRepository implements OrderRepositoryContract
{
    public function create(Order $order, array $items): Order
    {
        $order->save();
        $order->items()->saveMany($items);

        return $order->load(['items', 'user']);
    }

    public function findByUuid(string $uuid): ?Order
    {
        return Order::query()
            ->with(['items', 'user'])
            ->where('uuid', $uuid)
            ->first();
    }
}
