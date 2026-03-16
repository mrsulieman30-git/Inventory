<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\DispenseStockRequest;
use App\Http\Requests\ReceiveStockRequest;
use App\Services\Inventory\InventoryService;
use App\Services\Clinical\ClinicalDecisionSupportService;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class InventoryController
 * Exposes core inventory management functionalities securely via RESTful JSON endpoints.
 */
class InventoryController extends Controller
{
    private InventoryService $inventoryService;
    private ClinicalDecisionSupportService $cdsService;

    public function __construct(InventoryService $inventoryService, ClinicalDecisionSupportService $cdsService)
    {
        $this->inventoryService = $inventoryService;
        $this->cdsService = $cdsService;
    }

    /**
     * Receive new stock into inventory.
     * Accessible by Pharmacists, Lab Techs, and Admins via policy.
     */
    public function receive(ReceiveStockRequest $request): JsonResponse
    {
        // Policy Authorization
        if ($request->user()->cannot('receive', \App\Models\Batch::class)) {
            return response()->json(['error' => 'Unauthorized. Only admins, pharmacists, and lab techs can receive stock.'], 403);
        }

        $validated = $request->validated();

        try {
            $batchData = [
                'unit_cost'          => $validated['unit_cost'] ?? 0,
                'selling_price'      => $validated['selling_price'] ?? 0,
                'manufacturing_date' => $validated['manufacturing_date'] ?? null,
                'expiry_date'        => $validated['expiry_date'] ?? null,
                'supplier_name'      => $validated['supplier_name'] ?? null,
                'reason'             => $validated['reason'] ?? 'Manual Stock Receive',
            ];

            $batch = $this->inventoryService->receiveStock(
                itemId: $validated['item_id'],
                locationId: $validated['location_id'],
                quantity: $validated['quantity'],
                batchNumber: $validated['batch_number'],
                batchData: $batchData,
                userId: $request->user()?->id // Assume auth middleware is present
            );

            return response()->json([
                'message' => 'Stock received successfully',
                'data'    => $batch,
            ], 201);

        } catch (\Exception $e) {
            // Log real exception for ops, return safe message to client
            report($e);
            return response()->json(['error' => 'An error occurred while receiving stock.'], 500);
        }
    }

    /**
     * Dispense stock from a specific location using FEFO.
     * Incorporates V2 Clinical Decision Support (Allergy & Drug Interaction checks).
     */
    public function dispense(DispenseStockRequest $request): JsonResponse
    {
        // Policy Authorization
        if ($request->user()->cannot('dispense', \App\Models\Batch::class)) {
            return response()->json(['error' => 'Unauthorized. Only admins, pharmacists, and nurses can dispense stock.'], 403);
        }

        $validated = $request->validated();

        // V2 Feature: Clinical Decision Support
        $patientId = $request->input('patient_id'); // E.g., passed from the EMR/frontend
        if ($patientId) {
            $item = Item::findOrFail($validated['item_id']);
            $safetyCheck = $this->cdsService->performSafetyCheck($item, $patientId);

            // In a strict mode, we prevent dispensing entirely if not safe.
            // Or, we require an explicit 'override_reason' parameter to bypass.
            if (!$safetyCheck['is_safe'] && !$request->has('override_reason')) {
                return response()->json([
                    'error'    => 'Clinical Decision Support blocked this dispense due to safety concerns.',
                    'warnings' => $safetyCheck['warnings'],
                    'required' => 'override_reason', // Tells the UI to prompt for a pharmacist override password/reason
                ], 422);
            }
        }

        try {
            $dispensedLogs = $this->inventoryService->dispenseStock(
                itemId: $validated['item_id'],
                locationId: $validated['location_id'],
                quantity: $validated['quantity'],
                userId: $request->user()?->id,
                reason: $validated['reason'] ?? 'Manual Dispense'
            );

            return response()->json([
                'message' => 'Stock dispensed successfully using FEFO rules',
                'data'    => $dispensedLogs,
            ]);

        } catch (InsufficientStockException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            report($e);
            return response()->json(['error' => 'An error occurred while dispensing stock.'], 500);
        }
    }
}
