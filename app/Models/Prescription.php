<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Prescription
 * Represents a medication order / eMAR sync entity.
 *
 * @package App\Models
 */
class Prescription extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'patient_id',
        'prescriber_id',
        'prescription_number',
        'status',
        'location_id',
        'clinical_notes',
    ];

    /**
     * Get the doctor/user who prescribed the medication.
     */
    public function prescriber(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prescriber_id');
    }

    /**
     * Get the location (ward/pharmacy) associated with the prescription.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the line items of the prescription.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }
}
