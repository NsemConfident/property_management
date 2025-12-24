<?php

use App\Models\Property;
use App\Models\Unit;
use App\Models\Tenant;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public $properties;
    public $totalProperties;
    public $totalUnits;
    public $occupiedUnits;
    public $totalRevenue;
    public $monthlyRevenue;

    public function mount(): void
    {
        $user = Auth::user();
        
        if (!$user->isOwner()) {
            abort(403, 'Unauthorized access.');
        }

        $this->loadData();
    }

    public function loadData(): void
    {
        $this->properties = Property::where('owner_id', Auth::id())
            ->with(['units', 'units.currentTenant'])
            ->get();

        $this->totalProperties = $this->properties->count();
        
        $this->totalUnits = Unit::whereIn('property_id', $this->properties->pluck('id'))->count();
        
        $this->occupiedUnits = Tenant::whereIn('unit_id', Unit::whereIn('property_id', $this->properties->pluck('id'))->pluck('id'))
            ->where('lease_status', 'active')
            ->count();

        // Get all tenant IDs for owned properties
        $tenantIds = Tenant::whereIn('unit_id', Unit::whereIn('property_id', $this->properties->pluck('id'))->pluck('id'))
            ->pluck('id');

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

<div>
<x-layouts.app :title="__('Property Owner Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <!-- Stats Cards -->
        <div class="grid gap-4 md:grid-cols-4">
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <h3 class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Total Properties</h3>
                <p class="mt-2 text-3xl font-bold text-neutral-900 dark:text-neutral-100">
                    {{ $totalProperties }}
                </p>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <h3 class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Total Units</h3>
                <p class="mt-2 text-3xl font-bold text-neutral-900 dark:text-neutral-100">
                    {{ $totalUnits }}
                </p>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <h3 class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Occupied Units</h3>
                <p class="mt-2 text-3xl font-bold text-neutral-900 dark:text-neutral-100">
                    {{ $occupiedUnits }}
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
            <h2 class="mb-4 text-xl font-semibold text-neutral-900 dark:text-neutral-100">My Properties</h2>
            @if($properties->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Property Name</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Address</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Units</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($properties as $property)
                                <tr class="border-b border-neutral-100 dark:border-neutral-800">
                                    <td class="px-4 py-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $property->name }}</td>
                                    <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $property->address }}, {{ $property->city }}</td>
                                    <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $property->units->count() }}</td>
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
                <p class="text-neutral-600 dark:text-neutral-400">No properties found. Add your first property to get started.</p>
            @endif
        </div>

        <!-- Revenue Summary -->
        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <h2 class="mb-4 text-xl font-semibold text-neutral-900 dark:text-neutral-100">Revenue Summary</h2>
            <div class="grid gap-4 md:grid-cols-2">
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
    </div>
</x-layouts.app>
</div>