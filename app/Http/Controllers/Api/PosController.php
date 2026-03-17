<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Pos\PosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoint for retail checkout flows and POS registers.
 */
class PosController extends Controller
{
    private PosService $posService;

    public function __construct(PosService $posService)
    {
        $this->posService = $posService;
    }

    /**
     * Complete a sale, applying discounts, taxes, and updating inventory.
     * Cashiers, Pharmacists, and Admins can process sales.
     */
    public function checkout(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'items'            => 'required|array|min:1',
            'items.*.item_id'  => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price'=>'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'location_id'      => 'required|exists:locations,id',
            'payment_method'   => 'required|string|in:cash,card,insurance',
            'patient_id'       => 'nullable|string',
            'global_discount'  => 'nullable|numeric|min:0'
        ]);

        try {
            $sale = $this->posService->processSale(
                items:          $validated['items'],
                locationId:     $validated['location_id'],
                cashierId:      $user ? $user->id : 1, // Fallback for testing
                paymentMethod:  $validated['payment_method'],
                patientId:      $validated['patient_id'] ?? null,
                globalDiscount: $validated['global_discount'] ?? 0
            );

            return response()->json([
                'message' => 'Sale processed successfully.',
                'receipt' => $sale->load('items')
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
