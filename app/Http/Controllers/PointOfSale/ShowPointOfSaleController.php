<?php

declare(strict_types=1);

namespace App\Http\Controllers\PointOfSale;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

final class ShowPointOfSaleController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('pos/index', [
            'paymentMethods' => PaymentMethod::options(),
        ]);
    }
}
