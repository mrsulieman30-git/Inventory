<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AnalyticsService;
use App\Services\Analytics\PredictiveAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    private AnalyticsService $analyticsService;
    private PredictiveAnalyticsService $predictiveService;

    public function __construct(AnalyticsService $analyticsService, PredictiveAnalyticsService $predictiveService)
    {
        $this->analyticsService = $analyticsService;
        $this->predictiveService = $predictiveService;
    }

    /**
     * Get real-time financial valuation of all stock.
     */
    public function valuation(Request $request): JsonResponse
    {
        if ($request->user() && !$request->user()->hasRole('admin')) {
             return response()->json(['error' => 'Unauthorized.'], 403);
        }
        $value = $this->analyticsService->getInventoryValuation($request->input('location_id'));
        return response()->json(['total_valuation' => $value]);
    }

    /**
     * Get fast-moving items.
     */
    public function fastMoving(Request $request): JsonResponse
    {
        $items = $this->analyticsService->getFastMovingItems(
            (int) $request->input('days', 30),
            (int) $request->input('limit', 10)
        );
        return response()->json(['data' => $items]);
    }

    /**
     * Get financial losses from wastage.
     */
    public function wastage(Request $request): JsonResponse
    {
        $report = $this->analyticsService->getWastageReport((int) $request->input('days', 30));
        return response()->json(['data' => $report]);
    }

    /**
     * Predict future stockouts based on historical consumption velocity.
     * V4.0 Intelligence Feature.
     */
    public function predictiveStockouts(Request $request): JsonResponse
    {
        // Enforce policy (Admins and Procurement)
        if ($request->user() && !in_array($request->user()->role, ['admin', 'pharmacist'])) {
             return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $lookbackDays = (int) $request->input('lookback_days', 30);
        $predictions = $this->predictiveService->predictDaysOfSupply($lookbackDays);

        return response()->json([
            'message' => "Stockout predictions based on past {$lookbackDays} days of consumption velocity.",
            'data'    => $predictions,
        ]);
    }
}
