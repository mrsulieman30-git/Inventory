<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TemperatureLog;
use App\Models\VirtualRack;
use App\Models\Batch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Handles incoming webhooks from physical IoT sensors placed in medical fridges.
 * Performs automated actions based on critical thresholds.
 */
class IotWebhookController extends Controller
{
    /**
     * Receive temperature payload from an IoT device.
     * Expects a JSON payload containing the rack ID and temperature.
     */
    public function receiveTemperature(Request $request): JsonResponse
    {
        // Typically, IoT endpoints use a secret key or HMAC signature for auth instead of Sanctum tokens
        if ($request->header('X-IoT-Secret') !== env('IOT_WEBHOOK_SECRET', 'dev-secret-123')) {
            return response()->json(['error' => 'Unauthorized device'], 401);
        }

        $validated = $request->validate([
            'virtual_rack_id' => 'required|exists:virtual_racks,id',
            'temperature_celsius' => 'required|numeric',
            'humidity_percentage' => 'nullable|numeric',
            'recorded_at' => 'nullable|date',
        ]);

        $temp = $validated['temperature_celsius'];
        $rackId = $validated['virtual_rack_id'];

        $status = 'normal';
        $systemAction = null;

        // Standard cold chain for vaccines is 2°C to 8°C
        if ($temp < 2.0 || $temp > 8.0) {
            $status = ($temp < 0.0 || $temp > 15.0) ? 'critical' : 'warning';

            // If it hits a critical failure, immediately protect patients by locking the stock.
            if ($status === 'critical') {
                $affectedBatches = Batch::where('virtual_rack_id', $rackId)
                                        ->where('status', 'available')
                                        ->update(['status' => 'quarantined']);

                $systemAction = "QUARANTINED {$affectedBatches} batches due to critical temperature breach ({$temp}°C).";
                Log::alert($systemAction);

                // Fire Event -> send SMS to Lead Pharmacist immediately
            }
        }

        TemperatureLog::create([
            'virtual_rack_id'     => $rackId,
            'temperature_celsius' => $temp,
            'humidity_percentage' => $validated['humidity_percentage'] ?? null,
            'status'              => $status,
            'system_action'       => $systemAction,
            'recorded_at'         => $validated['recorded_at'] ?? now(),
        ]);

        return response()->json(['message' => 'Telemetry logged successfully', 'action_taken' => $systemAction], 200);
    }
}
