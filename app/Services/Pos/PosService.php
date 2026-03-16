<?php

namespace App\Services\Pos;

use App\Models\PosSale;
use App\Models\PosSaleItem;
use App\Services\Inventory\InventoryService;
use App\Exceptions\InsufficientStockException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Class PosService
 * Manages Retail Pharmacy point of sale dispensing, integrating financials with atomic inventory decrementing.
 */
class PosService
{
    private InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Process a checkout/sale at the POS terminal.
     * Decrements inventory and logs the financial transaction simultaneously.
     *
     * @param array $items Array of items sold: [['item_id' => 1, 'quantity' => 2, 'unit_price' => 15.00, 'discount' => 0]]
     * @param int $locationId Store/Branch where the sale occurred
     * @param int $cashierId User ID of the cashier
     * @param string $paymentMethod e.g., 'cash', 'card'
     * @param string|null $patientId Optional patient ID for history/loyalty
     * @param float $globalDiscount Optional discount applied to the entire bill
     * @return PosSale
     */
    public function processSale(
        array $items,
        int $locationId,
        int $cashierId,
        string $paymentMethod = 'cash',
        ?string $patientId = null,
        float $globalDiscount = 0
    ): PosSale {
        return DB::transaction(function () use ($items, $locationId, $cashierId, $paymentMethod, $patientId, $globalDiscount) {

            $subtotal = 0;
            $saleLines = [];

            // 1. Validate & Prepare Items (Without locking DB yet)
            foreach ($items as $itemData) {
                $qty = (int)$itemData['quantity'];
                $price = (float)$itemData['unit_price'];
                $itemDiscount = (float)($itemData['discount'] ?? 0);

                $lineTotal = ($price * $qty) - $itemDiscount;
                $subtotal += $lineTotal;

                $saleLines[] = [
                    'item_id' => $itemData['item_id'],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'discount' => $itemDiscount,
                    'line_total' => $lineTotal,
                ];
            }

            // 2. Financial Calculations
            // Let's assume a generic 8% sales tax for the retail environment
            $taxRate = 0.08;
            $taxableAmount = max(0, $subtotal - $globalDiscount);
            $taxAmount = $taxableAmount * $taxRate;
            $totalAmount = $taxableAmount + $taxAmount;

            // 3. Create the Parent Record
            $sale = PosSale::create([
                'receipt_number'  => 'REC-' . strtoupper(Str::random(8)) . '-' . time(),
                'location_id'     => $locationId,
                'patient_id'      => $patientId,
                'cashier_id'      => $cashierId,
                'subtotal'        => $subtotal,
                'discount_amount' => $globalDiscount,
                'tax_amount'      => $taxAmount,
                'total_amount'    => $totalAmount,
                'payment_method'  => $paymentMethod,
                'status'          => 'completed'
            ]);

            // 4. Decrement Inventory & Create Line Items
            foreach ($saleLines as $line) {
                // Core atomic inventory deduction (FEFO logic applied)
                try {
                    $dispenseLog = $this->inventoryService->dispenseStock(
                        itemId: $line['item_id'],
                        locationId: $locationId,
                        quantity: $line['quantity'],
                        userId: $cashierId,
                        reason: "POS Retail Sale (Receipt: {$sale->receipt_number})"
                    );
                } catch (InsufficientStockException $e) {
                    // DB Transaction rolls back automatically, preventing partial financial sales when stock is out
                    throw new \Exception("Cannot process sale: " . $e->getMessage());
                }

                PosSaleItem::create([
                    'pos_sale_id'     => $sale->id,
                    'item_id'         => $line['item_id'],
                    'quantity'        => $line['quantity'],
                    'unit_price'      => $line['unit_price'],
                    'discount'        => $line['discount'],
                    'line_total'      => $line['line_total'],
                    'transaction_log' => json_encode($dispenseLog) // Keep reference to exact batches sold for returns
                ]);
            }

            return $sale;
        });
    }
}
