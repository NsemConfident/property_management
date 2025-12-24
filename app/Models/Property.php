<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    protected $fillable = [
        'owner_id',
        'manager_id',
        'name',
        'description',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'type',
        'total_units',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_units' => 'integer',
        ];
    }

    /**
     * Get the owner of the property
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the manager of the property
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get all units in this property
     */
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * Get active units
     */
    public function activeUnits(): HasMany
    {
        return $this->hasMany(Unit::class)->where('status', 'available');
    }
}
