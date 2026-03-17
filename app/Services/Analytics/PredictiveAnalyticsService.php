<?php

namespace App\Services\Analytics;

use App\Models\Batch;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Class PredictiveAnalyticsService
 * Evaluates historical velocity to forecast future stock-outs.
 */
class PredictiveAnalyticsService
{
    /**
     * Calculate the "Days of Supply" remaining for each item.
     * Uses a rolling average of consumption over the past $lookbackDays.
     *
     * @param int $lookbackDays The historical window to analyze (e.g., past 30 days).
     * @return \Illuminate\Support\Collection Items paired with their predicted exhaustion date.
     */
    public function predictDaysOfSupply(int $lookbackDays = 30)
    {
        $startDate = Carbon::now()->subDays($lookbackDays);

        // 1. Calculate Average Daily Consumption using the Immutable Transaction Log
        $consumptionRates = DB::table('transactions')
            ->where('type', 'dispense')
            ->where('created_at', '>=', $startDate)
            ->select('item_id', DB::raw("SUM(quantity) / {$lookbackDays} as average_daily_usage"))
            ->groupBy('item_id')
            ->pluck('average_daily_usage', 'item_id');

        // 2. Get Current Available Stock for all active items
        $currentStock = Batch::available()
            ->select('item_id', DB::raw('SUM(current_quantity) as total_available'))
            ->groupBy('item_id')
            ->pluck('total_available', 'item_id');

        $predictions = collect();

        // 3. Map the data and predict stockout dates
        foreach ($currentStock as $itemId => $availableQty) {
            $dailyUsage = $consumptionRates->get($itemId, 0);

            if ($dailyUsage > 0) {
                // If usage exists, calculate days remaining
                $daysRemaining = floor($availableQty / $dailyUsage);
                $estimatedStockoutDate = Carbon::now()->addDays($daysRemaining)->toDateString();
            } else {
                // If it hasn't been used in the lookback period, it has infinite (or stagnant) supply
                $daysRemaining = null;
                $estimatedStockoutDate = 'N/A - Slow Mover';
            }

            $predictions->push([
                'item_id'                 => $itemId,
                'current_stock'           => (int) $availableQty,
                'average_daily_usage'     => round((float) $dailyUsage, 2),
                'predicted_days_remaining'=> $daysRemaining,
                'estimated_stockout_date' => $estimatedStockoutDate,
                'status'                  => $this->determineHealthStatus($daysRemaining)
            ]);
        }

        // Sort by the items that will run out the fastest
        return $predictions->sortBy('predicted_days_remaining')->values();
    }

    /**
     * Categorize the health of the stock level based on days remaining.
     */
    protected function determineHealthStatus(?int $daysRemaining): string
    {
        if ($daysRemaining === null) return 'stagnant';
        if ($daysRemaining <= 7) return 'critical_danger';
        if ($daysRemaining <= 14) return 'warning_low';
        return 'healthy';
    }
}
