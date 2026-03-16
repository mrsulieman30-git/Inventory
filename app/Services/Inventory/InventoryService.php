<?php

namespace App\Services\Inventory;

use App\Exceptions\InsufficientStockException;
use App\Models\Batch;
use App\Models\Item;
use App\Models\Location;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Class InventoryService
 * Handles the core business logic for the enterprise inventory system (Pharmacy & Lab).
 * Ensures consistency, ACID compliance via transactions, and creates immutable audit trails.
 */
class InventoryService
{
    /**
     * Add stock to a specific location (Stock In).
     *
     * @param int $itemId
     * @param int $locationId
     * @param int $quantity
     * @param string $batchNumber
     * @param array $batchData Additional data like expiry date, cost, supplier.
     * @param int|null $userId
     * @return Batch
     */
    public function receiveStock(
        int $itemId,
        int $locationId,
        int $quantity,
        string $batchNumber,
        array $batchData = [],
        ?int $userId = null
    ): Batch {
        return DB::transaction(function () use ($itemId, $locationId, $quantity, $batchNumber, $batchData, $userId) {

            // 1. Find or create the batch at the specified location
            $batch = Batch::firstOrCreate(
                [
                    'item_id'      => $itemId,
                    'location_id'  => $locationId,
                    'batch_number' => $batchNumber,
                ],
                array_merge([
                    'initial_quantity' => 0,
                    'current_quantity' => 0,
                    'status'           => 'available',
                ], $batchData)
            );

            // 2. Update the batch quantity
            $batch->increment('current_quantity', $quantity);

            // If it was the first time, initial quantity should be updated if it was 0
            if ($batch->wasRecentlyCreated || $batch->initial_quantity === 0) {
                 $batch->initial_quantity = $batch->current_quantity;
                 $batch->save();
            }

            // 3. Record the transaction (Audit trail)
            $this->logTransaction(
                type: 'stock_in',
                itemId: $itemId,
                batchId: $batch->id,
                quantity: $quantity,
                balanceAfter: $batch->current_quantity,
                sourceLocationId: null, // From external supplier
                destinationLocationId: $locationId,
                userId: $userId,
                unitCost: $batch->unit_cost ?? 0,
                reason: $batchData['reason'] ?? 'Received stock from supplier'
            );

            return $batch;
        });
    }

    /**
     * Dispense stock from a specific location (Stock Out).
     * Uses FEFO (First Expiring, First Out) if batch ID is not specifically provided.
     *
     * @param int $itemId
     * @param int $locationId
     * @param int $quantity
     * @param int|null $userId
     * @param string $reason
     * @return array Array of batches dispensed and quantities
     * @throws InsufficientStockException
     */
    public function dispenseStock(
        int $itemId,
        int $locationId,
        int $quantity,
        ?int $userId = null,
        string $reason = 'Dispense to patient / ward'
    ): array {
        return DB::transaction(function () use ($itemId, $locationId, $quantity, $userId, $reason) {

            $item = Item::findOrFail($itemId);

            // Check total available stock at the location
            $totalAvailable = Batch::where('item_id', $itemId)
                ->where('location_id', $locationId)
                ->available()
                ->sum('current_quantity');

            if ($totalAvailable < $quantity) {
                throw new InsufficientStockException($item->name, $quantity, $totalAvailable);
            }

            // Fetch batches ordered by nearest expiry date (FEFO)
            $batches = Batch::where('item_id', $itemId)
                ->where('location_id', $locationId)
                ->available()
                ->orderByRaw('expiry_date IS NULL') // Put null expiry dates at the end
                ->orderBy('expiry_date', 'asc')
                ->lockForUpdate() // Prevent race conditions
                ->get();

            $remainingQuantityToDispense = $quantity;
            $dispensedLog = [];

            foreach ($batches as $batch) {
                if ($remainingQuantityToDispense <= 0) {
                    break;
                }

                $quantityToTakeFromBatch = min($batch->current_quantity, $remainingQuantityToDispense);

                // Deduct from batch
                $batch->decrement('current_quantity', $quantityToTakeFromBatch);

                // If batch is empty, update status
                if ($batch->current_quantity === 0) {
                    $batch->update(['status' => 'depleted']);
                }

                // Log the transaction
                $this->logTransaction(
                    type: 'dispense',
                    itemId: $itemId,
                    batchId: $batch->id,
                    quantity: $quantityToTakeFromBatch,
                    balanceAfter: $batch->current_quantity,
                    sourceLocationId: $locationId,
                    destinationLocationId: null, // Out to patient/ward
                    userId: $userId,
                    unitCost: $batch->unit_cost ?? 0,
                    reason: $reason
                );

                $remainingQuantityToDispense -= $quantityToTakeFromBatch;
                $dispensedLog[] = [
                    'batch_id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'quantity' => $quantityToTakeFromBatch
                ];
            }

            return $dispensedLog;
        });
    }

    /**
     * Transfer stock between two internal locations.
     */
    public function transferStock(
        int $batchId,
        int $sourceLocationId,
        int $destinationLocationId,
        int $quantity,
        ?int $userId = null
    ): Batch {
        return DB::transaction(function () use ($batchId, $sourceLocationId, $destinationLocationId, $quantity, $userId) {

            $sourceBatch = Batch::where('id', $batchId)
                ->where('location_id', $sourceLocationId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($sourceBatch->current_quantity < $quantity) {
                 throw new InsufficientStockException($sourceBatch->item->name, $quantity, $sourceBatch->current_quantity);
            }

            // Deduct from source
            $sourceBatch->decrement('current_quantity', $quantity);
            if ($sourceBatch->current_quantity === 0) {
                $sourceBatch->update(['status' => 'depleted']);
            }

            // Add to destination
            $destinationBatch = Batch::firstOrCreate(
                [
                    'item_id'      => $sourceBatch->item_id,
                    'location_id'  => $destinationLocationId,
                    'batch_number' => $sourceBatch->batch_number,
                ],
                [
                    'initial_quantity'   => 0,
                    'current_quantity'   => 0,
                    'unit_cost'          => $sourceBatch->unit_cost,
                    'selling_price'      => $sourceBatch->selling_price,
                    'manufacturing_date' => $sourceBatch->manufacturing_date,
                    'expiry_date'        => $sourceBatch->expiry_date,
                    'supplier_name'      => $sourceBatch->supplier_name,
                    'status'             => 'available',
                ]
            );

            $destinationBatch->increment('current_quantity', $quantity);

            // Log Transaction Out
            $this->logTransaction(
                type: 'transfer_out',
                itemId: $sourceBatch->item_id,
                batchId: $sourceBatch->id,
                quantity: $quantity,
                balanceAfter: $sourceBatch->current_quantity,
                sourceLocationId: $sourceLocationId,
                destinationLocationId: $destinationLocationId,
                userId: $userId,
                unitCost: $sourceBatch->unit_cost,
                reason: "Transfer to Location ID: {$destinationLocationId}"
            );

            // Log Transaction In
            $this->logTransaction(
                type: 'transfer_in',
                itemId: $destinationBatch->item_id,
                batchId: $destinationBatch->id,
                quantity: $quantity,
                balanceAfter: $destinationBatch->current_quantity,
                sourceLocationId: $sourceLocationId,
                destinationLocationId: $destinationLocationId,
                userId: $userId,
                unitCost: $destinationBatch->unit_cost,
                reason: "Transfer from Location ID: {$sourceLocationId}"
            );

            return $destinationBatch;
        });
    }

    /**
     * Create an immutable transaction log.
     */
    protected function logTransaction(
        string $type,
        int $itemId,
        int $batchId,
        int $quantity,
        int $balanceAfter,
        ?int $sourceLocationId,
        ?int $destinationLocationId,
        ?int $userId,
        float $unitCost,
        string $reason
    ): Transaction {
        return Transaction::create([
            'reference_number'        => 'TXN-' . strtoupper(Str::random(10)) . '-' . time(),
            'type'                    => $type,
            'item_id'                 => $itemId,
            'batch_id'                => $batchId,
            'source_location_id'      => $sourceLocationId,
            'destination_location_id' => $destinationLocationId,
            'quantity'                => $quantity,
            'balance_after'           => $balanceAfter,
            'reason'                  => $reason,
            'user_id'                 => $userId,
            'unit_cost'               => $unitCost,
            'total_cost'              => $unitCost * $quantity,
        ]);
    }
}
