<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'user_id',
        'unit_id',
        'lease_start_date',
        'lease_end_date',
        'monthly_rent',
        'lease_status',
        'emergency_contact_name',
        'emergency_contact_phone',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'lease_start_date' => 'date',
            'lease_end_date' => 'date',
            'monthly_rent' => 'decimal:2',
        ];
    }

    /**
     * Get the user associated with this tenant
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the unit this tenant is renting
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get all payments made by this tenant
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get all invoices for this tenant
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get all reminders for this tenant
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }

    /**
     * Check if lease is active
     */
    public function isActive(): bool
    {
        return $this->lease_status === 'active';
    }

    /**
     * Get property through unit
     */
    public function property()
    {
        return $this->unit->property ?? null;
    }
}
