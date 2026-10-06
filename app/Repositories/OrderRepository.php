<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepository as OrderRepositoryContract;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;

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

    public function listCreatedBetween(CarbonInterface $from, CarbonInterface $to): Collection
    {
        return Order::query()
            ->with('user')
            ->withCount('items')
            ->whereBetween('created_at', [$from, $to])
            ->latest()
            ->latest('id')
            ->get();
    }
}
