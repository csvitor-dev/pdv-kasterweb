<?php

use App\Http\Controllers\Orders\ShowOrderReceiptController;
use App\Http\Controllers\PointOfSale\ShowPointOfSaleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', ShowPointOfSaleController::class)->name('home');

    Route::get('orders/{uuid}', ShowOrderReceiptController::class)
        ->whereUuid('uuid')
        ->name('orders.show');
});

require __DIR__.'/settings.php';
