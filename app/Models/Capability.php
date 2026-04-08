<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Capability extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'details',
        'featured',
        'coming_soon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'details' => 'array',
        'featured' => 'boolean',
        'coming_soon' => 'boolean',
        'is_active' => 'boolean',
    ];
}
