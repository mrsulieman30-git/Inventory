<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VirtualRack extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'name',
        'bin_number',
        'is_cold_storage',
        'is_secure',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_cold_storage' => 'boolean',
        'is_secure'       => 'boolean',
        'is_active'       => 'boolean',
        'sort_order'      => 'integer',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get all batches currently stored on this virtual rack.
     */
    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }
}
