<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'model_type',
        'model_id',
        'user_id',
        'ip_address',
        'old_values',
        'new_values',
        'user_agent',
        'reason',
        'previous_hash',
        'hash',
        'signature',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];
}
