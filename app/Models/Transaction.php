<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Transaction
 * The log for all stock movements and adjustments, serving as an immutable audit trail.
 *
 * @package App\Models
 */
class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference_number',
        'type',
        'item_id',
        'batch_id',
        'source_location_id',
        'destination_location_id',
        'quantity',
        'balance_after',
        'reason',
        'user_id',
        'unit_cost',
        'total_cost',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity'      => 'integer',
        'balance_after' => 'integer',
        'unit_cost'     => 'decimal:2',
        'total_cost'    => 'decimal:2',
    ];

    /**
     * Get the item associated with the transaction.
     *
     * @return BelongsTo
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the batch associated with the transaction.
     *
     * @return BelongsTo
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Get the source location from where stock was taken.
     *
     * @return BelongsTo
     */
    public function sourceLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'source_location_id');
    }

    /**
     * Get the destination location where stock was moved to.
     *
     * @return BelongsTo
     */
    public function destinationLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }

    /**
     * Get the user who performed the transaction.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
