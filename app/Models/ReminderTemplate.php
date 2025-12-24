<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReminderTemplate extends Model
{
    protected $fillable = [
        'name',
        'type',
        'subject',
        'message',
        'is_active',
        'days_before',
        'variables_help',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'days_before' => 'integer',
        ];
    }

    /**
     * Get active template for a specific type
     */
    public static function getActiveForType(string $type, ?int $daysBefore = null): ?self
    {
        $query = self::where('type', $type)
            ->where('is_active', true);

        if ($daysBefore !== null) {
            $query->where(function ($q) use ($daysBefore) {
                $q->whereNull('days_before')
                  ->orWhere('days_before', $daysBefore);
            });
        }

        return $query->orderBy('days_before', 'desc')->first();
    }

    /**
     * Get available template variables
     */
    public static function getAvailableVariables(string $type): array
    {
        $common = [
            '{{tenant_name}}' => 'Tenant full name',
            '{{tenant_email}}' => 'Tenant email address',
            '{{property_name}}' => 'Property name',
            '{{unit_number}}' => 'Unit number',
            '{{property_address}}' => 'Property full address',
        ];

        $typeSpecific = match ($type) {
            'payment_due', 'payment_overdue' => [
                '{{invoice_number}}' => 'Invoice number',
                '{{invoice_date}}' => 'Invoice date',
                '{{due_date}}' => 'Due date',
                '{{amount_due}}' => 'Amount due (formatted)',
                '{{amount_balance}}' => 'Outstanding balance (formatted)',
                '{{days_until_due}}' => 'Days until due date',
                '{{days_overdue}}' => 'Days overdue',
            ],
            'lease_expiry' => [
                '{{lease_start_date}}' => 'Lease start date',
                '{{lease_end_date}}' => 'Lease end date',
                '{{monthly_rent}}' => 'Monthly rent amount (formatted)',
                '{{days_until_expiry}}' => 'Days until lease expiry',
            ],
            default => [],
        };

        return array_merge($common, $typeSpecific);
    }
}
