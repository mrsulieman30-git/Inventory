<?php

namespace App\Jobs;

use App\Models\Item;
use App\Models\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Class CheckLowStockJob
 * Proactively checks if the total available stock for an item falls below its minimum_stock_level threshold.
 */
class CheckLowStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     * Uses an optimized, single-query aggregate to prevent N+1 queries.
     */
    public function handle(): void
    {
        // 1. Get all items with minimum stock levels set
        $itemsWithThresholds = Item::where('is_active', true)
            ->whereNotNull('minimum_stock_level')
            ->where('minimum_stock_level', '>', 0)
            ->get(['id', 'name', 'minimum_stock_level'])
            ->keyBy('id');

        if ($itemsWithThresholds->isEmpty()) {
            return;
        }

        // 2. Perform a single aggregate query to get the total available stock for those items
        $stockAggregates = Batch::selectRaw('item_id, SUM(current_quantity) as total_quantity')
            ->whereIn('item_id', $itemsWithThresholds->keys())
            ->available()
            ->groupBy('item_id')
            ->pluck('total_quantity', 'item_id');

        // 3. Process the results, checking if stock falls below the threshold
        foreach ($itemsWithThresholds as $itemId => $item) {
            // If the item doesn't exist in the batches query, its total stock is 0
            $totalStock = $stockAggregates->get($itemId, 0);

            if ($totalStock < $item->minimum_stock_level) {
                // In an enterprise system, this triggers an automated Purchase Order (PO)
                // or sends alerts to the procurement/pharmacy managers.
                Log::alert(sprintf(
                    'LOW STOCK ALERT: Item [%s] has dropped below minimum threshold. Current: %d, Minimum Allowed: %d.',
                    $item->name,
                    $totalStock,
                    $item->minimum_stock_level
                ));

                // Dispatch notification event (e.g., event(new LowStockEvent($item, $totalStock));)
            }
        }
    }
}
