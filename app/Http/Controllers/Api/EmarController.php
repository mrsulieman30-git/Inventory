<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Item;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * Controller to handle incoming Electronic Medical Record (EMR/eMAR) payloads.
 * Acts as the bridge between hospital software and the pharmacy inventory.
 */
class EmarController extends Controller
{
    /**
     * Accept a simplified FHIR-like JSON payload representing a new MedicationOrder.
     */
    public function receiveMedicationOrder(Request $request): JsonResponse
    {
        // Require specific interoperability token
        if ($request->bearerToken() !== env('EMAR_INTEGRATION_TOKEN', 'dev-emar-token-456')) {
            return response()->json(['error' => 'Unauthorized EMR connection.'], 401);
        }

        // Validate the incoming JSON payload (Simplified FHIR MedicationRequest structure)
        $validated = $request->validate([
            'patient_identifier' => 'required|string',
            'prescriber_npi'     => 'required|string', // National Provider Identifier
            'ward_location_id'   => 'required|exists:locations,id',
            'clinical_notes'     => 'nullable|string',

            // Medication details
            'medications'                 => 'required|array|min:1',
            'medications.*.sku'           => 'required|exists:items,sku',
            'medications.*.quantity'      => 'required|integer|min:1',
            'medications.*.dosage_instructions' => 'required|string',
            'medications.*.duration_days' => 'nullable|integer',
        ]);

        try {
            // Find the prescriber (Doctor) by their external NPI or generic ID
            // In a real system, you'd have an NPI column. We'll fallback to a system user for V4 prototype.
            $prescriber = User::where('role', 'admin')->first(); // Fallback Mock

            $prescription = DB::transaction(function () use ($validated, $prescriber) {
                // 1. Create the parent Prescription Header
                $prescription = Prescription::create([
                    'prescription_number' => 'RX-' . strtoupper(Str::random(10)),
                    'patient_id'          => $validated['patient_identifier'],
                    'prescriber_id'       => $prescriber->id ?? 1,
                    'location_id'         => $validated['ward_location_id'],
                    'clinical_notes'      => $validated['clinical_notes'] ?? null,
                    'status'              => 'pending',
                ]);

                // 2. Parse and attach the line items
                foreach ($validated['medications'] as $medData) {
                    $item = Item::where('sku', $medData['sku'])->firstOrFail();

                    PrescriptionItem::create([
                        'prescription_id'     => $prescription->id,
                        'item_id'             => $item->id,
                        'prescribed_quantity' => $medData['quantity'],
                        'dosage_instructions' => $medData['dosage_instructions'],
                        'duration_days'       => $medData['duration_days'] ?? null,
                        'status'              => 'pending',
                    ]);
                }

                return $prescription;
            });

            Log::info("eMAR: Received new MedicationOrder [{$prescription->prescription_number}] for Patient [{$validated['patient_identifier']}].");

            return response()->json([
                'message' => 'MedicationOrder accepted and queued for pharmacy review.',
                'prescription_number' => $prescription->prescription_number,
                'status' => 'pending'
            ], 201);

        } catch (\Exception $e) {
            Log::error("eMAR Integration Error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to process EMR payload.'], 500);
        }
    }
}
