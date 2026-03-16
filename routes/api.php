<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\PurchaseOrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Secure routes protected by Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // User Info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Inventory V1 Endpoints
    Route::prefix('inventory')->group(function () {
        Route::post('/receive', [InventoryController::class, 'receive']);
        Route::post('/dispense', [InventoryController::class, 'dispense']);
    });

    // V2 Analytics Endpoints
    Route::prefix('analytics')->group(function () {
        Route::get('/valuation', [AnalyticsController::class, 'valuation']);
        Route::get('/fast-moving', [AnalyticsController::class, 'fastMoving']);
        Route::get('/wastage', [AnalyticsController::class, 'wastage']);
    });

    // V2 Procurement / Purchase Orders
    Route::prefix('procurement')->group(function () {
        Route::post('/orders/{id}/approve', [PurchaseOrderController::class, 'approve']);
    });
});
