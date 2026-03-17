<?php

namespace App\Traits;

use App\Observers\AuditObserver;

/**
 * Trait Auditable
 *
 * Automatically attaches the AuditObserver to Eloquent models, ensuring all creations,
 * updates, and deletions are securely logged to the immutable `audit_logs` table.
 * Crucial for healthcare compliance (HIPAA, DEA).
 */
trait Auditable
{
    /**
     * Boot the Auditable trait for a model.
     *
     * @return void
     */
    public static function bootAuditable()
    {
        static::observe(AuditObserver::class);
    }
}
