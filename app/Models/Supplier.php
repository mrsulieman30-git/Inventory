<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'rating',
        'is_active',
        'is_preferred',
    ];

    protected $casts = [
        'rating'       => 'decimal:2',
        'is_active'    => 'boolean',
        'is_preferred' => 'boolean',
    ];
}
