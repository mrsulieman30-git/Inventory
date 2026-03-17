<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemperatureLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'virtual_rack_id',
        'temperature_celsius',
        'humidity_percentage',
        'status',
        'system_action',
        'recorded_at',
    ];

    protected $casts = [
        'temperature_celsius' => 'decimal:2',
        'humidity_percentage' => 'decimal:2',
        'recorded_at'         => 'datetime',
    ];

    public function virtualRack(): BelongsTo
    {
        return $this->belongsTo(VirtualRack::class);
    }
}
