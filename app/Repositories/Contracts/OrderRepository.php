<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Order;
use App\Models\OrderItem;

interface OrderRepository
{
    /**
     * Persist the order together with its items.
     *
     * @param  list<OrderItem>  $items
     */
    public function create(Order $order, array $items): Order;

    public function findByUuid(string $uuid): ?Order;
}
