<?php

declare(strict_types=1);

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Services\Order\Contracts\OrderService;
use Inertia\Inertia;
use Inertia\Response;

final class ListTodayOrdersController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    public function __invoke(): Response
    {
        $today = now(config('app.business_timezone'));
        $orders = $this->orderService->listForDay($today);

        return Inertia::render('orders/index', [
            'date' => $today->toDateString(),
            'orders' => OrderResource::collection($orders)->resolve(),
            'summary' => [
                'orders_count' => $orders->count(),
                'total_cents' => (int) $orders->sum('total_cents'),
            ],
        ]);
    }
}
