<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'supplier_id',
        'purchase_order_id',
        'status',
        'total_amount',
        'amount_paid',
        'currency',
        'invoice_date',
        'due_date',
        'paid_at',
        'verified_by',
        'discrepancy_notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'amount_paid'  => 'decimal:2',
        'invoice_date' => 'date',
        'due_date'     => 'date',
        'paid_at'      => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
