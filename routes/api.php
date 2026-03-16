<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\ClinicalController;
use App\Http\Controllers\Api\VirtualRackController;
use App\Http\Controllers\Api\AuthController;

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

// Authentication (Public)
Route::post('/login', [AuthController::class, 'login']);

// Secure routes protected by Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Auth & User Info
    Route::post('/logout', [AuthController::class, 'logout']);
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

    // V3 Point of Sale (Retail)
    Route::prefix('pos')->group(function () {
        Route::post('/checkout', [PosController::class, 'checkout']);
    });

    // V3 Clinical Decision Support
    Route::prefix('clinical')->group(function () {
        Route::post('/interactions', [ClinicalController::class, 'storeInteraction']);
    });

    // V3 Virtual Racks (Sorting & Bins)
    Route::prefix('virtual-racks')->group(function () {
        Route::post('/', [VirtualRackController::class, 'store']);
        Route::post('/assign-batch', [VirtualRackController::class, 'assignBatch']);
    });
});
