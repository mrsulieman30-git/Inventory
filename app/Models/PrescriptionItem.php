<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PrescriptionItem
 * Represents a line item inside a medication order.
 *
 * @package App\Models
 */
class PrescriptionItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'prescription_id',
        'item_id',
        'prescribed_quantity',
        'dispensed_quantity',
        'dosage_instructions',
        'duration_days',
        'status',
        'transaction_log',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'prescribed_quantity' => 'integer',
        'dispensed_quantity'  => 'integer',
        'duration_days'       => 'integer',
        'transaction_log'     => 'array',
    ];

    /**
     * Get the prescription that owns the line item.
     */
    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    /**
     * Get the item (medication) being prescribed.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
