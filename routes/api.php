<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InventoryController;

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
});
