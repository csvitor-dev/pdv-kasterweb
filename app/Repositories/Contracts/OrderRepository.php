<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;

interface OrderRepository
{
    /**
     * Persist the order together with its items.
     *
     * @param  list<OrderItem>  $items
     */
    public function create(Order $order, array $items): Order;

    public function findByUuid(string $uuid): ?Order;

    /**
     * Orders created in the given period, newest first, with operator and item count.
     *
     * @return Collection<int, Order>
     */
    public function listCreatedBetween(CarbonInterface $from, CarbonInterface $to): Collection;
}
