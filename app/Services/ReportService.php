<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get revenue report by period
     */
    public function getRevenueReport(array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? now()->startOfYear();
        $endDate = $filters['end_date'] ?? now()->endOfYear();
        $propertyId = $filters['property_id'] ?? null;

        $query = Payment::where('status', 'completed')
            ->whereBetween('payment_date', [$startDate, $endDate]);

        if ($propertyId) {
            $query->whereHas('tenant.unit', function ($q) use ($propertyId) {
                $q->where('property_id', $propertyId);
            });
        }

        $payments = $query->get();

        // Monthly breakdown
        $monthlyData = $payments->groupBy(function ($payment) {
            return Carbon::parse($payment->payment_date)->format('Y-m');
        })->map(function ($group) {
            return $group->sum('amount');
        })->sortKeys();

        return [
            'total_revenue' => $payments->sum('amount'),
            'total_payments' => $payments->count(),
            'monthly_breakdown' => $monthlyData,
            'average_payment' => $payments->count() > 0 ? $payments->avg('amount') : 0,
        ];
    }

    /**
     * Get revenue by property
     */
    public function getRevenueByProperty(array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? now()->startOfYear();
        $endDate = $filters['end_date'] ?? now()->endOfYear();

        $revenue = Payment::where('payments.status', 'completed')
            ->whereBetween('payments.payment_date', [$startDate, $endDate])
            ->join('tenants', 'payments.tenant_id', '=', 'tenants.id')
            ->join('units', 'tenants.unit_id', '=', 'units.id')
            ->join('properties', 'units.property_id', '=', 'properties.id')
            ->select('properties.id', 'properties.name', DB::raw('SUM(payments.amount) as total_revenue'), DB::raw('COUNT(payments.id) as payment_count'))
            ->groupBy('properties.id', 'properties.name')
            ->orderBy('total_revenue', 'desc')
            ->get();

        return $revenue->toArray();
    }

    /**
     * Get occupancy rates
     */
    public function getOccupancyRates(array $filters = []): array
    {
        $propertyId = $filters['property_id'] ?? null;

        $query = Unit::query();

        if ($propertyId) {
            $query->where('property_id', $propertyId);
        }

        $totalUnits = $query->count();
        $occupiedUnits = $query->where('status', 'occupied')->count();
        $availableUnits = $query->where('status', 'available')->count();
        $maintenanceUnits = $query->where('status', 'maintenance')->count();

        $occupancyRate = $totalUnits > 0 ? ($occupiedUnits / $totalUnits) * 100 : 0;

        // By property
        $byProperty = Unit::when($propertyId, function ($q) use ($propertyId) {
            $q->where('property_id', $propertyId);
        })
            ->join('properties', 'units.property_id', '=', 'properties.id')
            ->select('properties.id', 'properties.name', DB::raw('COUNT(units.id) as total_units'), DB::raw('SUM(CASE WHEN units.status = "occupied" THEN 1 ELSE 0 END) as occupied_units'))
            ->groupBy('properties.id', 'properties.name')
            ->get()
            ->map(function ($item) {
                $item->occupancy_rate = $item->total_units > 0 ? ($item->occupied_units / $item->total_units) * 100 : 0;
                return $item;
            });

        return [
            'total_units' => $totalUnits,
            'occupied_units' => $occupiedUnits,
            'available_units' => $availableUnits,
            'maintenance_units' => $maintenanceUnits,
            'occupancy_rate' => round($occupancyRate, 2),
            'by_property' => $byProperty,
        ];
    }

    /**
     * Get payment history
     */
    public function getPaymentHistory(array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? now()->subMonths(6);
        $endDate = $filters['end_date'] ?? now();
        $propertyId = $filters['property_id'] ?? null;
        $tenantId = $filters['tenant_id'] ?? null;

        $query = Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->with(['tenant.user', 'tenant.unit.property', 'invoice']);

        if ($propertyId) {
            $query->whereHas('tenant.unit', function ($q) use ($propertyId) {
                $q->where('property_id', $propertyId);
            });
        }

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $payments = $query->orderBy('payment_date', 'desc')->get();

        // Group by payment method
        $byMethod = $payments->where('status', 'completed')
            ->groupBy('payment_method')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => $group->sum('amount'),
                ];
            });

        return [
            'payments' => $payments,
            'total_amount' => $payments->where('status', 'completed')->sum('amount'),
            'by_method' => $byMethod,
            'by_status' => $payments->groupBy('status')->map->count(),
        ];
    }

    /**
     * Get overdue invoices report
     */
    public function getOverdueInvoices(array $filters = []): array
    {
        $query = Invoice::where('due_date', '<', now())
            ->whereIn('status', ['draft', 'sent', 'partially_paid'])
            ->with(['tenant.user', 'tenant.unit.property']);

        if (isset($filters['property_id'])) {
            $query->whereHas('tenant.unit', function ($q) use ($filters) {
                $q->where('property_id', $filters['property_id']);
            });
        }

        $invoices = $query->orderBy('due_date', 'asc')->get();

        return [
            'invoices' => $invoices,
            'total_overdue' => $invoices->sum('balance'),
            'count' => $invoices->count(),
            'by_property' => $invoices->groupBy('tenant.unit.property.name')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => $group->sum('balance'),
                ];
            }),
        ];
    }

    /**
     * Get tenant payment history
     */
    public function getTenantPaymentHistory(int $tenantId, array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? now()->subYear();
        $endDate = $filters['end_date'] ?? now();

        $payments = Payment::where('tenant_id', $tenantId)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->with('invoice')
            ->orderBy('payment_date', 'desc')
            ->get();

        $invoices = Invoice::where('tenant_id', $tenantId)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->orderBy('invoice_date', 'desc')
            ->get();

        return [
            'payments' => $payments,
            'invoices' => $invoices,
            'total_paid' => $payments->where('status', 'completed')->sum('amount'),
            'total_invoiced' => $invoices->sum('amount'),
            'outstanding' => $invoices->sum('balance'),
        ];
    }
}

