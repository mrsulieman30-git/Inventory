<?php

namespace App\Jobs;

use App\Models\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Class CheckExpiringStockJob
 * Proactively checks for batches nearing their expiry date and alerts the system.
 */
class CheckExpiringStockJob implements ShouldQueue
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
     */
    public function handle(): void
    {
        // Find all available batches expiring in the next 90 days
        $expiringBatches = Batch::with(['item', 'location'])
            ->expiringSoon(90) // Using the model scope created earlier
            ->get();

        if ($expiringBatches->isEmpty()) {
            return;
        }

        foreach ($expiringBatches as $batch) {
            $daysUntilExpiry = now()->diffInDays($batch->expiry_date, false);

            // V2 Feature: Smart Auto-Quarantine (30 Days)
            // If the medication is expiring in less than 30 days, we lock it so it cannot be dispensed to patients.
            if ($daysUntilExpiry <= 30 && $batch->status === 'available') {
                $batch->update(['status' => 'quarantined']);

                Log::alert("AUTO-QUARANTINE: Item [{$batch->item->name}] in Location [{$batch->location->name}], Batch: {$batch->batch_number} has been quarantined because it expires in {$daysUntilExpiry} days.");
            } else {
                // Otherwise, just send a warning for procurement/pharmacy to monitor (31-90 days)
                Log::warning("EXPIRING STOCK ALERT: Item [{$batch->item->name}] in Location [{$batch->location->name}], Batch: {$batch->batch_number} expires in {$daysUntilExpiry} days.");
            }

            // Dispatch notification event (e.g., event(new StockExpiringEvent($batch));)
        }
    }
}
