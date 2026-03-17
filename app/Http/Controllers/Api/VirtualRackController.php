<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VirtualRack;
use App\Models\Batch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller to manage the physical/virtual layout of the pharmacy or lab.
 */
class VirtualRackController extends Controller
{
    /**
     * Create a new Virtual Rack/Bin.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user && !in_array($user->role, ['admin', 'pharmacist'])) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'location_id'     => 'required|exists:locations,id',
            'name'            => 'required|string|max:255',
            'bin_number'      => 'nullable|string|max:50',
            'is_cold_storage' => 'boolean',
            'is_secure'       => 'boolean',
            'sort_order'      => 'integer',
        ]);

        $rack = VirtualRack::create($validated);

        return response()->json([
            'message' => 'Virtual Rack created successfully.',
            'data'    => $rack,
        ], 201);
    }

    /**
     * Assign a batch of medication to a specific rack/bin to organize inventory.
     */
    public function assignBatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'virtual_rack_id' => 'required|exists:virtual_racks,id',
        ]);

        $batch = Batch::findOrFail($validated['batch_id']);
        $rack = VirtualRack::findOrFail($validated['virtual_rack_id']);

        // Optional logic: Verify that cold storage items go into cold storage racks
        // if ($batch->item->requires_cold_chain && !$rack->is_cold_storage) {
        //     return response()->json(['error' => 'Cannot assign a cold chain item to a non-refrigerated rack.'], 422);
        // }

        $batch->update([
            'virtual_rack_id' => $rack->id
        ]);

        return response()->json([
            'message' => "Batch successfully assigned to rack {$rack->name}.",
            'data'    => $batch
        ]);
    }
}
