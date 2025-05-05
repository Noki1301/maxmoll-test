<?php

use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\StockMovementController;

Route::get('/warehouses', [WarehouseController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/orders', [OrderController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);

Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/', [OrderController::class, 'store']);
    Route::put('/{id}', [OrderController::class, 'update']);

    Route::post('/{id}/complete', [OrderController::class, 'complete']);
    Route::post('/{id}/cancel', [OrderController::class, 'cancel']);
    Route::post('/{id}/resume', [OrderController::class, 'resume']);
});

Route::get('/movements', [StockMovementController::class, 'index']);
