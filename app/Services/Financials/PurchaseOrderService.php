<?php

namespace App\Services\Financials;

use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * Class PurchaseOrderService
 * Handles the generation, approval, and management of Purchase Orders, linking inventory needs to financial procurement.
 */
class PurchaseOrderService
{
    /**
     * Automate the creation of a Purchase Order for items that have dropped below their minimum stock levels.
     * Aggregates items by their preferred supplier to create consolidated POs.
     *
     * @param array<int, array> $lowStockItems An array of items that need restocking, with their required quantities.
     * @param int $deliveryLocationId The main warehouse/location where the stock should be delivered.
     * @return array<PurchaseOrder>
     */
    public function generateAutomatedPOs(array $lowStockItems, int $deliveryLocationId): array
    {
        $createdPOs = [];

        // Group the low stock items by their preferred supplier
        $supplierGroups = [];
        foreach ($lowStockItems as $itemData) {
            $item = Item::find($itemData['item_id']);
            if (!$item || !$item->preferred_supplier_id) {
                // In enterprise systems, items without preferred suppliers might trigger a separate alert for procurement to handle manually
                Log::warning("Automated PO: Item [{$item->name}] skipped. No preferred supplier linked.");
                continue;
            }

            $supplierGroups[$item->preferred_supplier_id][] = [
                'item'     => $item,
                'quantity' => $itemData['reorder_quantity']
            ];
        }

        // Generate a PO for each Supplier group
        foreach ($supplierGroups as $supplierId => $itemsToOrder) {
            $createdPOs[] = DB::transaction(function () use ($supplierId, $itemsToOrder, $deliveryLocationId) {

                $poNumber = 'PO-' . strtoupper(Str::random(6)) . '-' . date('Ymd');
                $subtotal = 0;

                // 1. Create the PO Draft
                $purchaseOrder = PurchaseOrder::create([
                    'po_number'    => $poNumber,
                    'supplier_id'  => $supplierId,
                    'location_id'  => $deliveryLocationId,
                    'is_automated' => true,
                    'status'       => 'pending_approval', // Needs human authorization
                    'notes'        => 'System generated PO due to low stock alerts.',
                ]);

                // 2. Add the Line Items
                foreach ($itemsToOrder as $orderData) {
                    /** @var Item $item */
                    $item = $orderData['item'];
                    $qty  = $orderData['quantity'];

                    // In a real system, you'd fetch the last negotiated price from a SupplierItem contract model.
                    // Here, we'll assume a theoretical 0.00 until procurement updates it, or a pre-defined average cost.
                    $estimatedUnitCost = 10.50; // Placeholder for logic
                    $lineTotal = $estimatedUnitCost * $qty;

                    PurchaseOrderItem::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'item_id'           => $item->id,
                        'ordered_quantity'  => $qty,
                        'unit_cost'         => $estimatedUnitCost,
                        'total_price'       => $lineTotal,
                    ]);

                    $subtotal += $lineTotal;
                }

                // 3. Update Financials
                // Let's assume a standard 5% tax rate for this example. This would normally be configurable per region/supplier.
                $taxAmount = $subtotal * 0.05;
                $purchaseOrder->update([
                    'subtotal'     => $subtotal,
                    'tax_amount'   => $taxAmount,
                    'total_amount' => $subtotal + $taxAmount,
                ]);

                return $purchaseOrder;
            });
        }

        return $createdPOs;
    }

    /**
     * Approves a draft/pending Purchase Order, moving it to 'approved' and ready to be sent.
     */
    public function approvePO(int $poId, int $approverUserId): PurchaseOrder
    {
        $po = PurchaseOrder::findOrFail($poId);

        // Ensure only pending or draft POs are approved
        if (!in_array($po->status, ['draft', 'pending_approval'])) {
            throw new \Exception("Purchase order is not in a draft state and cannot be approved.");
        }

        $po->update([
            'status'      => 'approved',
            'approved_by' => $approverUserId,
            'approved_at' => now(),
        ]);

        return $po;
    }
}
