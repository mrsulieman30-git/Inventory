<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Financials\PurchaseOrderService;
use App\Models\PurchaseOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    private PurchaseOrderService $poService;

    public function __construct(PurchaseOrderService $poService)
    {
        $this->poService = $poService;
    }

    /**
     * Approve a drafted/pending Purchase Order.
     * Accessible by Admins and Pharmacists.
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if ($user && !in_array($user->role, ['admin', 'pharmacist'])) {
            return response()->json(['error' => 'Unauthorized. Procurement approval required.'], 403);
        }

        try {
            $approvedPO = $this->poService->approvePO($id, $user ? $user->id : 1);

            return response()->json([
                'message' => 'Purchase Order successfully approved and ready to send to supplier.',
                'data'    => $approvedPO,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
