<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrugInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'primary_item_id',
        'secondary_item_id',
        'severity',
        'description',
        'clinical_recommendation',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function primaryItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'primary_item_id');
    }

    public function secondaryItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'secondary_item_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
