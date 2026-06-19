<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Career extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'location',
        'department',
        'employment_type',
        'excerpt',
        'description',
        'is_active',
        'sort_order',
        'closes_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'closes_at' => 'date',
    ];

    /**
     * @return HasMany<CareerApplication, $this>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(CareerApplication::class);
    }

    /**
     * @param  Builder<Career>  $query
     * @return Builder<Career>
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $q): void {
                $q->whereNull('closes_at')
                    ->orWhereDate('closes_at', '>=', now()->toDateString());
            });
    }
}
