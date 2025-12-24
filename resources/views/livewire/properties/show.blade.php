<?php

use App\Models\Property;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

use function Livewire\Volt\layout;

layout('components.layouts.app', ['title' => 'Property Details']);

new class extends Component {
    public Property $property;

    public function mount(Property $property): void
    {
        $user = Auth::user();
        
        if (!$user->isOwner() && !$user->isManager()) {
            abort(403, 'Unauthorized access.');
        }

        // Check authorization
        if ($user->isOwner() && $property->owner_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }
        if ($user->isManager() && $property->manager_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $this->property = $property->load(['owner', 'manager', 'units', 'units.currentTenant']);
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <flux:link href="{{ route('properties.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-2 text-sm text-neutral-600 dark:text-neutral-400">
                    <flux:icon.arrow-left class="size-4" />
                    Back to Properties
                </flux:link>
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ $property->name }}</h1>
                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">{{ $property->address }}, {{ $property->city }}</p>
            </div>
            <flux:link href="{{ route('properties.edit', $property) }}" variant="primary" wire:navigate>
                <flux:icon.pencil class="size-4" />
                Edit Property
            </flux:link>
        </div>

        <!-- Property Details -->
        <div class="grid gap-6 md:grid-cols-2">
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <h2 class="mb-4 text-lg font-semibold text-neutral-900 dark:text-neutral-100">Property Information</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Type</dt>
                        <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ ucfirst($property->type) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Status</dt>
                        <dd class="mt-1">
                            <span class="rounded-full px-2 py-1 text-xs font-medium
                                {{ $property->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                {{ $property->status === 'inactive' ? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' : '' }}
                                {{ $property->status === 'maintenance' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                            ">
                                {{ ucfirst($property->status) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Total Units</dt>
                        <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ $property->total_units }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Address</dt>
                        <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">
                            {{ $property->address }}<br>
                            {{ $property->city }}, {{ $property->state }}<br>
                            {{ $property->country }} {{ $property->postal_code }}
                        </dd>
                    </div>
                    @if($property->description)
                        <div>
                            <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Description</dt>
                            <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ $property->description }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <h2 class="mb-4 text-lg font-semibold text-neutral-900 dark:text-neutral-100">Management</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Owner</dt>
                        <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ $property->owner->name ?? 'N/A' }}</dd>
                    </div>
                    @if($property->manager)
                        <div>
                            <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Manager</dt>
                            <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ $property->manager->name }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Units -->
        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Units ({{ $property->units->count() }})</h2>
                <flux:link href="{{ route('units.create', ['property' => $property->id]) }}" variant="primary" wire:navigate>
                    <flux:icon.plus class="size-4" />
                    Add Unit
                </flux:link>
            </div>
            
            @if($property->units->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Unit Number</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Type</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Monthly Rent</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Status</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Tenant</th>
                                <th class="px-4 py-2 text-right text-sm font-medium text-neutral-600 dark:text-neutral-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($property->units as $unit)
                                <tr class="border-b border-neutral-100 dark:border-neutral-800">
                                    <td class="px-4 py-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $unit->unit_number }}</td>
                                    <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $unit->unit_type ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400">₦{{ number_format($unit->monthly_rent, 2) }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        <span class="rounded-full px-2 py-1 text-xs
                                            {{ $unit->status === 'available' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                            {{ $unit->status === 'occupied' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                            {{ $unit->status === 'maintenance' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                        ">
                                            {{ ucfirst($unit->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400">
                                        {{ $unit->currentTenant->user->name ?? 'Vacant' }}
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="flex items-center justify-end gap-2">
                                            <flux:link href="{{ route('units.show', $unit) }}" wire:navigate>
                                                <flux:icon.eye class="size-4" />
                                            </flux:link>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center text-neutral-600 dark:text-neutral-400">No units added yet.</p>
            @endif
        </div>
    </div>
</div>

