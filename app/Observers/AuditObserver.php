<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Class AuditObserver
 *
 * Captures the differences (old vs new values) when an Eloquent model is modified
 * and securely records them. It also logs the user, IP, and user-agent.
 */
class AuditObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        $this->logEvent('created', $model, null, $model->getAttributes());
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        // Get the original attributes before the change
        $oldValues = array_intersect_key($model->getOriginal(), $model->getDirty());
        // Get only the attributes that actually changed
        $newValues = $model->getDirty();

        $this->logEvent('updated', $model, $oldValues, $newValues);
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->logEvent('deleted', $model, $model->getAttributes(), null);
    }

    /**
     * Handle the Model "restored" event (SoftDeletes).
     */
    public function restored(Model $model): void
    {
        $this->logEvent('restored', $model, null, $model->getAttributes());
    }

    /**
     * Write the log entry and cryptographically chain it to the previous entry.
     * This creates a mathematically tamper-evident blockchain ledger.
     */
    protected function logEvent(string $eventType, Model $model, ?array $oldValues, ?array $newValues): void
    {
        // 1. Prepare Data Payload
        $payload = [
            'event_type'  => $eventType,
            'model_type'  => get_class($model),
            'model_id'    => $model->getKey(),
            'user_id'     => Auth::check() ? Auth::id() : null, // Null implies an automated system job
            'ip_address'  => Request::ip() ?? '127.0.0.1',
            'user_agent'  => Request::userAgent() ?? 'System',
            'old_values'  => $oldValues ? json_encode($oldValues) : null,
            'new_values'  => $newValues ? json_encode($newValues) : null,
        ];

        // 2. Fetch the Previous Hash
        $lastLog = AuditLog::latest('id')->first();
        $previousHash = $lastLog ? $lastLog->hash : hash('sha256', 'GENESIS_BLOCK');

        $payload['previous_hash'] = $previousHash;

        // 3. Generate the New Hash (SHA-256)
        // We hash the entire JSON payload + the previous hash to mathematically link them
        $dataToHash = json_encode($payload) . config('app.key');
        $payload['hash'] = hash('sha256', $dataToHash);

        // 4. Create the Record
        AuditLog::create($payload);
    }
}
