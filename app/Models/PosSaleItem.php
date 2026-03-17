<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosSaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pos_sale_id',
        'item_id',
        'prescription_item_id',
        'quantity',
        'unit_price',
        'discount',
        'line_total',
        'transaction_log',
        'status',
    ];

    protected $casts = [
        'quantity'        => 'integer',
        'unit_price'      => 'decimal:2',
        'discount'        => 'decimal:2',
        'line_total'      => 'decimal:2',
        'transaction_log' => 'array',
    ];

    public function posSale(): BelongsTo
    {
        return $this->belongsTo(PosSale::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
