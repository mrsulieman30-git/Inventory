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

        // In a real system, you would send notifications to users here (e.g. Email, Slack, In-App)
        // For V1, we log the details for the admin ops team.
        foreach ($expiringBatches as $batch) {
            Log::warning("EXPIRING STOCK ALERT: Item [{$batch->item->name}] in Location [{$batch->location->name}], Batch Number: {$batch->batch_number} expires on {$batch->expiry_date->toDateString()}. Quantity: {$batch->current_quantity}");

            // Dispatch notification event (e.g., event(new StockExpiringEvent($batch));)
        }
    }
}
