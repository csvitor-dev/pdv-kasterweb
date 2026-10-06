<?php

declare(strict_types=1);

namespace App\Services\Order\Contracts;

use App\Exceptions\Domain\Order\InsufficientPaymentException;
use App\Exceptions\Domain\Product\ProductUnavailableException;
use App\Models\Order;
use App\Services\Order\Inputs\CreateOrderInput;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
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

    /**
     * Orders of the business day that contains the given moment.
     *
     * @return Collection<int, Order>
     */
    public function listForDay(CarbonInterface $moment): Collection;
}
