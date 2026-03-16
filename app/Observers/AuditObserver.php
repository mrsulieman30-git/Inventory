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
     * Write the log entry.
     */
    protected function logEvent(string $eventType, Model $model, ?array $oldValues, ?array $newValues): void
    {
        AuditLog::create([
            'event_type'  => $eventType,
            'model_type'  => get_class($model),
            'model_id'    => $model->getKey(),
            'user_id'     => Auth::check() ? Auth::id() : null, // Null implies an automated system job
            'ip_address'  => Request::ip(),
            'user_agent'  => Request::userAgent(),
            'old_values'  => $oldValues ? json_encode($oldValues) : null,
            'new_values'  => $newValues ? json_encode($newValues) : null,
        ]);
    }
}
