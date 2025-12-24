<?php

use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    public function mount(): void
    {
        $user = Auth::user();
        
        if (!$user->isOwner() && !$user->isManager()) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function deleteProperty($propertyId): void
    {
        $property = Property::findOrFail($propertyId);
        
        // Check authorization
        $user = Auth::user();
        if ($user->isOwner() && $property->owner_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }
        if ($user->isManager() && $property->manager_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        // Check if property has units
        if ($property->units()->count() > 0) {
            session()->flash('error', 'Cannot delete property with existing units. Please remove all units first.');
            return;
        }

        $property->delete();
        session()->flash('success', 'Property deleted successfully.');
    }

    public function getPropertiesProperty()
    {
        $user = Auth::user();
        
        $query = Property::query();

        if ($user->isOwner()) {
            $query->where('owner_id', $user->id);
        } elseif ($user->isManager()) {
            $query->where('manager_id', $user->id);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('address', 'like', '%' . $this->search . '%')
                  ->orWhere('city', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        return $query->with(['owner', 'manager', 'units'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }
}; ?>

<x-layouts.app :title="__('Properties')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Properties</h1>
                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">Manage your properties</p>
            </div>
            <flux:link href="{{ route('properties.create') }}" variant="primary" wire:navigate>
                <flux:icon.plus class="size-4" />
                Add Property
            </flux:link>
        </div>

        <!-- Filters -->
        <div class="flex gap-4">
            <div class="flex-1">
                <flux:input 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search properties..." 
                    type="search"
                />
            </div>
            <div class="w-48">
                <flux:select wire:model.live="statusFilter" placeholder="Filter by status">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="maintenance">Maintenance</option>
                </flux:select>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if (session()->has('success'))
            <div class="rounded-lg bg-green-50 p-4 text-green-800 dark:bg-green-900/20 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="rounded-lg bg-red-50 p-4 text-red-800 dark:bg-red-900/20 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif

        <!-- Properties Table -->
        <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-neutral-200 dark:border-neutral-700">
                            <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Name</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Address</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Units</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Owner</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Status</th>
                            <th class="px-4 py-3 text-right text-sm font-medium text-neutral-600 dark:text-neutral-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->properties as $property)
                            <tr class="border-b border-neutral-100 dark:border-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-900/50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-neutral-900 dark:text-neutral-100">{{ $property->name }}</div>
                                    <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ ucfirst($property->type) }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                                    {{ $property->address }}, {{ $property->city }}
                                </td>
                                <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                                    {{ $property->units->count() }} units
                                </td>
                                <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                                    {{ $property->owner->name ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-1 text-xs font-medium
                                        {{ $property->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                        {{ $property->status === 'inactive' ? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' : '' }}
                                        {{ $property->status === 'maintenance' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                    ">
                                        {{ ucfirst($property->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <flux:link href="{{ route('properties.show', $property) }}" wire:navigate>
                                            <flux:icon.eye class="size-4" />
                                        </flux:link>
                                        <flux:link href="{{ route('properties.edit', $property) }}" wire:navigate>
                                            <flux:icon.pencil class="size-4" />
                                        </flux:link>
                                        <flux:button 
                                            variant="ghost" 
                                            wire:click="deleteProperty({{ $property->id }})"
                                            wire:confirm="Are you sure you want to delete this property?"
                                        >
                                            <flux:icon.trash class="size-4 text-red-600" />
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-neutral-600 dark:text-neutral-400">
                                    No properties found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="border-t border-neutral-200 px-4 py-3 dark:border-neutral-700">
                {{ $this->properties->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>

