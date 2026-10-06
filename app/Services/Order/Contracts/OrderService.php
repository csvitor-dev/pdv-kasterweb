<?php

declare(strict_types=1);

namespace App\Services\Order\Contracts;

use App\Exceptions\Domain\Order\InsufficientPaymentException;
use App\Exceptions\Domain\Product\ProductUnavailableException;
use App\Models\Order;
use App\Services\Order\Inputs\CreateOrderInput;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface OrderService
{
    /**
     * Create an already finalized sale, with prices and totals computed from the catalog.
     *
     * @throws ProductUnavailableException
     * @throws InsufficientPaymentException
     */
    public function create(CreateOrderInput $input): Order;

    /**
     * @throws ModelNotFoundException<Order>
     */
    public function findByUuidOrFail(string $uuid): Order;
}
