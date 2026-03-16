<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'location_id',
        'created_by',
        'approved_by',
        'status',
        'subtotal',
        'tax_amount',
        'total_amount',
        'currency',
        'is_automated',
        'notes',
        'expected_delivery_date',
        'approved_at',
    ];

    protected $casts = [
        'is_automated'           => 'boolean',
        'expected_delivery_date' => 'datetime',
        'approved_at'            => 'datetime',
        'subtotal'               => 'decimal:2',
        'tax_amount'             => 'decimal:2',
        'total_amount'           => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
