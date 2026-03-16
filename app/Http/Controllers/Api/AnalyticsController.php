<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    private AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Get real-time financial valuation of all stock.
     */
    public function valuation(Request $request): JsonResponse
    {
        // Enforce policy (Admins only)
        if ($request->user() && !$request->user()->hasRole('admin')) {
             return response()->json(['error' => 'Unauthorized. Only admins can view financials.'], 403);
        }

        $locationId = $request->input('location_id');
        $value = $this->analyticsService->getInventoryValuation($locationId);

        return response()->json([
            'message' => 'Inventory valuation calculated using WAC.',
            'total_valuation' => $value,
        ]);
    }

    /**
     * Get fast-moving items.
     */
    public function fastMoving(Request $request): JsonResponse
    {
        // Enforce policy (Admins and Pharmacists)
        if ($request->user() && !in_array($request->user()->role, ['admin', 'pharmacist'])) {
             return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $days = (int) $request->input('days', 30);
        $limit = (int) $request->input('limit', 10);

        $items = $this->analyticsService->getFastMovingItems($days, $limit);

        return response()->json([
            'data' => $items,
        ]);
    }

    /**
     * Get financial losses from wastage.
     */
    public function wastage(Request $request): JsonResponse
    {
        if ($request->user() && !$request->user()->hasRole('admin')) {
             return response()->json(['error' => 'Unauthorized. Only admins can view financials.'], 403);
        }

        $days = (int) $request->input('days', 30);
        $report = $this->analyticsService->getWastageReport($days);

        return response()->json([
            'data' => $report,
        ]);
    }
}
