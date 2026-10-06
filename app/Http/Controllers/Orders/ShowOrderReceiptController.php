<?php

declare(strict_types=1);

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Services\Order\Contracts\OrderService;
use Inertia\Inertia;
use Inertia\Response;

final class ShowOrderReceiptController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    public function __invoke(string $uuid): Response
    {
        return Inertia::render('orders/show', [
            'order' => OrderResource::make($this->orderService->findByUuidOrFail($uuid))->resolve(),
        ]);
    }
}
