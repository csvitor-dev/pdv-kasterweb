<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Services\Order\Contracts\OrderService;

final class ShowOrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    public function __invoke(string $uuid): OrderResource
    {
        return OrderResource::make($this->orderService->findByUuidOrFail($uuid));
    }
}
