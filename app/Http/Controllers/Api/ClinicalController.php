<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Item;

/**
 * Controller to manage dynamic drug interactions and drug info.
 */
class ClinicalController extends Controller
{
    /**
     * Store a new drug interaction rule.
     * Accessible by Admins and Clinical Pharmacists.
     */
    public function storeInteraction(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user && !in_array($user->role, ['admin', 'pharmacist'])) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'primary_item_id' => 'required|exists:items,id',
            'secondary_item_id' => 'required|exists:items,id|different:primary_item_id',
            'severity' => 'required|in:mild,moderate,severe,contraindicated',
            'description' => 'required|string',
            'clinical_recommendation' => 'nullable|string',
        ]);

        try {
            DB::table('drug_interactions')->insert([
                'primary_item_id' => $validated['primary_item_id'],
                'secondary_item_id' => $validated['secondary_item_id'],
                'severity' => $validated['severity'],
                'description' => $validated['description'],
                'clinical_recommendation' => $validated['clinical_recommendation'],
                'created_by' => $user ? $user->id : null,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json(['message' => 'Drug interaction saved successfully.'], 201);
        } catch (\Exception $e) {
            // E.g., Unique constraint violation if rule already exists
            return response()->json(['error' => 'Interaction rule already exists or an error occurred.'], 422);
        }
    }
}
