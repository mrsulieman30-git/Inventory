<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Batch
 * Represents a specific batch/lot of an item at a specific location, tracking quantities, dates, and costs.
 *
 * @package App\Models
 */
class Batch extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'item_id',
        'location_id',
        'batch_number',
        'initial_quantity',
        'current_quantity',
        'unit_cost',
        'selling_price',
        'manufacturing_date',
        'expiry_date',
        'supplier_name',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'initial_quantity'   => 'integer',
        'current_quantity'   => 'integer',
        'unit_cost'          => 'decimal:2',
        'selling_price'      => 'decimal:2',
        'manufacturing_date' => 'date',
        'expiry_date'        => 'date',
    ];

    /**
     * Get the item that this batch belongs to.
     *
     * @return BelongsTo
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the location where this batch is stored.
     *
     * @return BelongsTo
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Scope a query to only include available batches.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
                     ->where('current_quantity', '>', 0);
    }

    /**
     * Scope a query to only include batches nearing expiry.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpiringSoon($query, int $days = 90)
    {
        return $query->whereNotNull('expiry_date')
                     ->where('expiry_date', '<=', now()->addDays($days))
                     ->where('expiry_date', '>', now());
    }
}
