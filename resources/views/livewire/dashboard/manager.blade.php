<?php

use App\Models\Property;

use function Livewire\Volt\layout;

layout('components.layouts.app', ['title' => __('Property Manager Dashboard')]);
use App\Models\Tenant;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public $properties;
    public $totalProperties;
    public $totalTenants;
    public $pendingInvoices;
    public $overdueInvoices;
    public $totalRevenue;
    public $monthlyRevenue;

    public function mount(): void
    {
        $user = Auth::user();
        
        if (!$user->isManager()) {
            abort(403, 'Unauthorized access.');
        }

        $this->loadData();
    }

    public function loadData(): void
    {
        $this->properties = Property::where('manager_id', Auth::id())
            ->with(['units', 'units.currentTenant'])
            ->get();

        $this->totalProperties = $this->properties->count();
        
        $unitIds = \App\Models\Unit::whereIn('property_id', $this->properties->pluck('id'))->pluck('id');
        
        $this->totalTenants = Tenant::whereIn('unit_id', $unitIds)
            ->where('lease_status', 'active')
            ->count();

        $tenantIds = Tenant::whereIn('unit_id', $unitIds)->pluck('id');

        $this->pendingInvoices = Invoice::whereIn('tenant_id', $tenantIds)
            ->where('status', 'sent')
            ->count();

        $this->overdueInvoices = Invoice::whereIn('tenant_id', $tenantIds)
            ->where(function($query) {
                $query->where('status', 'overdue')
                    ->orWhere(function($q) {
                        $q->where('due_date', '<', now())
                            ->where('status', '!=', 'paid');
                    });
            })
            ->count();

        $this->totalRevenue = Payment::whereIn('tenant_id', $tenantIds)
            ->where('status', 'completed')
            ->sum('amount');

        $this->monthlyRevenue = Payment::whereIn('tenant_id', $tenantIds)
            ->where('status', 'completed')
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
        <!-- Stats Cards -->
        <div class="grid gap-4 md:grid-cols-4">
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <h3 class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Managed Properties</h3>
                <p class="mt-2 text-3xl font-bold text-neutral-900 dark:text-neutral-100">
                    {{ $totalProperties }}
                </p>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <h3 class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Active Tenants</h3>
                <p class="mt-2 text-3xl font-bold text-neutral-900 dark:text-neutral-100">
                    {{ $totalTenants }}
                </p>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <h3 class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Overdue Invoices</h3>
                <p class="mt-2 text-3xl font-bold text-red-600 dark:text-red-400">
                    {{ $overdueInvoices }}
                </p>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <h3 class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Monthly Revenue</h3>
                <p class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">
                    ₦{{ number_format($monthlyRevenue, 2) }}
                </p>
            </div>
        </div>

        <!-- Properties List -->
        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <h2 class="mb-4 text-xl font-semibold text-neutral-900 dark:text-neutral-100">Managed Properties</h2>
            @if($properties->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Property Name</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Owner</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Address</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($properties as $property)
                                <tr class="border-b border-neutral-100 dark:border-neutral-800">
                                    <td class="px-4 py-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $property->name }}</td>
                                    <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $property->owner->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $property->address }}, {{ $property->city }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        <span class="rounded-full px-2 py-1 text-xs {{ $property->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                            {{ ucfirst($property->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-neutral-600 dark:text-neutral-400">No properties assigned to manage.</p>
            @endif
        </div>

        <!-- Summary -->
        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <h2 class="mb-4 text-xl font-semibold text-neutral-900 dark:text-neutral-100">Revenue Summary</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Total Revenue (All Time)</p>
                        <p class="mt-2 text-2xl font-bold text-neutral-900 dark:text-neutral-100">
                            ₦{{ number_format($totalRevenue, 2) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">This Month's Revenue</p>
                        <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">
                            ₦{{ number_format($monthlyRevenue, 2) }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <h2 class="mb-4 text-xl font-semibold text-neutral-900 dark:text-neutral-100">Invoice Status</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Pending Invoices</p>
                        <p class="mt-2 text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                            {{ $pendingInvoices }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Overdue Invoices</p>
                        <p class="mt-2 text-2xl font-bold text-red-600 dark:text-red-400">
                            {{ $overdueInvoices }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>