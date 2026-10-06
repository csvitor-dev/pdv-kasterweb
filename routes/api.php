<?php

use App\Http\Controllers\Api\Orders\ShowOrderController;
use App\Http\Controllers\Api\Orders\StoreOrderController;
use App\Http\Controllers\Api\Products\SearchProductsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->name('api.')->group(function () {
    Route::get('/products', SearchProductsController::class)
        ->name('products.search');

    Route::post('/orders', StoreOrderController::class)
        ->name('orders.store');
    Route::get('/orders/{uuid}', ShowOrderController::class)
        ->whereUuid('uuid')
        ->name('orders.show');
});
