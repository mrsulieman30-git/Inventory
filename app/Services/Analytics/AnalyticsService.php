<?php

namespace App\Services\Analytics;

use App\Models\Batch;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Class AnalyticsService
 * Provides powerful insights for inventory value, wastage, and fast-moving items.
 * Built to perform complex analytical queries efficiently.
 */
class AnalyticsService
{
    /**
     * Get the total financial valuation of all stock currently in the system.
     * Uses Weighted Average Cost (WAC) methodology by multiplying current quantities by their respective batch unit costs.
     *
     * @param int|null $locationId Filter by specific location/ward.
     * @return float The total valuation amount.
     */
    public function getInventoryValuation(?int $locationId = null): float
    {
        $query = Batch::available();

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        // We use selectRaw to push the heavy math (Quantity * Cost) down to the database level
        $result = $query->selectRaw('SUM(current_quantity * unit_cost) as total_value')->value('total_value');

        return (float) $result ?: 0.00;
    }

    /**
     * Generate a report of the fastest-moving items (highest dispensed quantity) over a given period.
     * Essential for cycle counting and optimizing reorder points.
     *
     * @param int $days Number of days to look back.
     * @param int $limit How many top items to return.
     * @return \Illuminate\Support\Collection
     */
    public function getFastMovingItems(int $days = 30, int $limit = 10)
    {
        $startDate = Carbon::now()->subDays($days);

        // Analyze immutable transaction logs to find true consumption rates
        return DB::table('transactions')
            ->join('items', 'transactions.item_id', '=', 'items.id')
            ->where('transactions.type', 'dispense') // Only count actual usage, not transfers
            ->where('transactions.created_at', '>=', $startDate)
            ->select(
                'items.id as item_id',
                'items.name as item_name',
                DB::raw('SUM(transactions.quantity) as total_dispensed')
            )
            ->groupBy('items.id', 'items.name')
            ->orderByDesc('total_dispensed')
            ->limit($limit)
            ->get();
    }

    /**
     * Calculate the financial cost of wastage due to expiry or damage within a period.
     *
     * @param int $days Number of days to analyze.
     * @return array Total value lost and the breakdown by item category.
     */
    public function getWastageReport(int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);

        // In a real system, you'd have a specific transaction type like 'wastage' or 'expired'.
        // For this example, we'll assume any 'stock_out' with a reason containing 'expired' or 'damaged' counts.
        $wastageTransactions = DB::table('transactions')
            ->join('items', 'transactions.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->where('transactions.type', 'adjustment') // Assuming negative adjustments for waste
            ->where(function ($query) {
                $query->where('transactions.reason', 'LIKE', '%expired%')
                      ->orWhere('transactions.reason', 'LIKE', '%damaged%');
            })
            ->where('transactions.created_at', '>=', $startDate)
            ->select(
                'categories.name as category_name',
                DB::raw('SUM(transactions.total_cost) as value_lost')
            )
            ->groupBy('categories.name')
            ->get();

        $totalLost = $wastageTransactions->sum('value_lost');

        return [
            'total_financial_loss' => (float) $totalLost,
            'breakdown_by_category' => $wastageTransactions
        ];
    }
}
