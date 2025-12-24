<?php

use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public Unit $unit;

    public function mount(Unit $unit): void
    {
        $user = Auth::user();
        
        if (!$user->isOwner() && !$user->isManager() && !$user->isTenant()) {
            abort(403, 'Unauthorized access.');
        }

        // Check authorization
        if ($user->isOwner() && $unit->property->owner_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }
        if ($user->isManager() && $unit->property->manager_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }
        if ($user->isTenant() && $user->tenant && $user->tenant->unit_id !== $unit->id) {
            abort(403, 'Unauthorized.');
        }

        $this->unit = $unit->load(['property', 'property.owner', 'property.manager', 'currentTenant', 'currentTenant.user']);
    }
}; ?>

<x-layouts.app :title="__('Unit Details')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <flux:link href="{{ route('properties.show', $unit->property) }}" wire:navigate class="mb-4 inline-flex items-center gap-2 text-sm text-neutral-600 dark:text-neutral-400">
                    <flux:icon.arrow-left class="size-4" />
                    Back to Property
                </flux:link>
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Unit {{ $unit->unit_number }}</h1>
                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">{{ $unit->property->name }}</p>
            </div>
            @if(auth()->user()->isOwner() || auth()->user()->isManager())
                <flux:link href="{{ route('units.edit', $unit) }}" variant="primary" wire:navigate>
                    <flux:icon.pencil class="size-4" />
                    Edit Unit
                </flux:link>
            @endif
        </div>

        <!-- Unit Details -->
        <div class="grid gap-6 md:grid-cols-2">
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <h2 class="mb-4 text-lg font-semibold text-neutral-900 dark:text-neutral-100">Unit Information</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Unit Number</dt>
                        <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ $unit->unit_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Unit Type</dt>
                        <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ $unit->unit_type ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Monthly Rent</dt>
                        <dd class="mt-1 text-sm font-semibold text-neutral-900 dark:text-neutral-100">₦{{ number_format($unit->monthly_rent, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Deposit</dt>
                        <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">₦{{ number_format($unit->deposit, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Status</dt>
                        <dd class="mt-1">
                            <span class="rounded-full px-2 py-1 text-xs font-medium
                                {{ $unit->status === 'available' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                {{ $unit->status === 'occupied' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                {{ $unit->status === 'maintenance' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                {{ $unit->status === 'reserved' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : '' }}
                            ">
                                {{ ucfirst($unit->status) }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <h2 class="mb-4 text-lg font-semibold text-neutral-900 dark:text-neutral-100">Unit Specifications</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Bedrooms</dt>
                        <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ $unit->bedrooms }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Bathrooms</dt>
                        <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ $unit->bathrooms }}</dd>
                    </div>
                    @if($unit->square_feet)
                        <div>
                            <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Square Feet</dt>
                            <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ number_format($unit->square_feet, 2) }} sq ft</dd>
                        </div>
                    @endif
                    @if($unit->description)
                        <div>
                            <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Description</dt>
                            <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ $unit->description }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Current Tenant -->
        @if($unit->currentTenant)
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <h2 class="mb-4 text-lg font-semibold text-neutral-900 dark:text-neutral-100">Current Tenant</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Name</dt>
                        <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ $unit->currentTenant->user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Email</dt>
                        <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ $unit->currentTenant->user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Lease Start Date</dt>
                        <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ $unit->currentTenant->lease_start_date->format('M d, Y') }}</dd>
                    </div>
                    @if($unit->currentTenant->lease_end_date)
                        <div>
                            <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Lease End Date</dt>
                            <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ $unit->currentTenant->lease_end_date->format('M d, Y') }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Lease Status</dt>
                        <dd class="mt-1">
                            <span class="rounded-full px-2 py-1 text-xs font-medium
                                {{ $unit->currentTenant->lease_status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                            ">
                                {{ ucfirst($unit->currentTenant->lease_status) }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>
        @else
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <p class="text-center text-neutral-600 dark:text-neutral-400">No tenant assigned to this unit.</p>
            </div>
        @endif
    </div>
</x-layouts.app>

