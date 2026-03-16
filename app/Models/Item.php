<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Item
 * The core entity representing a specific product (Medicine, Reagent, Consumable).
 *
 * @package App\Models
 */
class Item extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sku',
        'name',
        'description',
        'category_id',
        'type',
        'unit_of_measure',
        // Pharmacy specifics
        'is_prescription_required',
        'is_controlled_substance',
        'generic_name',
        'strength',
        'form',
        // Lab specifics
        'requires_cold_chain',
        'storage_conditions',
        // Settings
        'minimum_stock_level',
        'maximum_stock_level',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_prescription_required' => 'boolean',
        'is_controlled_substance'  => 'boolean',
        'requires_cold_chain'      => 'boolean',
        'is_active'                => 'boolean',
        'minimum_stock_level'      => 'integer',
        'maximum_stock_level'      => 'integer',
    ];

    /**
     * Get the category that owns the item.
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the batches for this item.
     *
     * @return HasMany
     */
    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    /**
     * Get all transactions related to this item.
     *
     * @return HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
