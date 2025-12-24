<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Unit extends Model
{
    protected $fillable = [
        'property_id',
        'unit_number',
        'unit_type',
        'monthly_rent',
        'deposit',
        'bedrooms',
        'bathrooms',
        'square_feet',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'monthly_rent' => 'decimal:2',
            'deposit' => 'decimal:2',
            'bedrooms' => 'integer',
            'bathrooms' => 'integer',
            'square_feet' => 'decimal:2',
        ];
    }

    /**
     * Get the property this unit belongs to
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the current tenant for this unit
     */
    public function currentTenant(): HasOne
    {
        return $this->hasOne(Tenant::class)->where('lease_status', 'active');
    }

    /**
     * Get all tenants for this unit
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    /**
     * Check if unit is available
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }
}
