<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $propertyFilter = '';

    public function mount(): void
    {
        $user = Auth::user();
        
        if (!$user->isOwner() && !$user->isManager()) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function terminateLease($tenantId): void
    {
        $tenant = Tenant::findOrFail($tenantId);
        
        // Check authorization
        $user = Auth::user();
        if ($user->isOwner()) {
            $propertyIds = \App\Models\Property::where('owner_id', $user->id)->pluck('id');
            $unitIds = \App\Models\Unit::whereIn('property_id', $propertyIds)->pluck('id');
            if (!in_array($tenant->unit_id, $unitIds->toArray())) {
                abort(403, 'Unauthorized.');
            }
        }
        if ($user->isManager()) {
            $propertyIds = \App\Models\Property::where('manager_id', $user->id)->pluck('id');
            $unitIds = \App\Models\Unit::whereIn('property_id', $propertyIds)->pluck('id');
            if (!in_array($tenant->unit_id, $unitIds->toArray())) {
                abort(403, 'Unauthorized.');
            }
        }

        $tenant->update([
            'lease_status' => 'terminated',
            'lease_end_date' => now(),
        ]);

        // Update unit status to available
        $tenant->unit->update(['status' => 'available']);

        session()->flash('success', 'Lease terminated successfully.');
    }

    public function getTenantsProperty()
    {
        $user = Auth::user();
        
        $query = Tenant::query()->with(['user', 'unit', 'unit.property']);

        if ($user->isOwner()) {
            $propertyIds = \App\Models\Property::where('owner_id', $user->id)->pluck('id');
            $unitIds = \App\Models\Unit::whereIn('property_id', $propertyIds)->pluck('id');
            $query->whereIn('unit_id', $unitIds);
        } elseif ($user->isManager()) {
            $propertyIds = \App\Models\Property::where('manager_id', $user->id)->pluck('id');
            $unitIds = \App\Models\Unit::whereIn('property_id', $propertyIds)->pluck('id');
            $query->whereIn('unit_id', $unitIds);
        }

        if ($this->search) {
            $query->whereHas('user', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            })->orWhereHas('unit', function($q) {
                $q->where('unit_number', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter) {
            $query->where('lease_status', $this->statusFilter);
        }

        if ($this->propertyFilter) {
            $query->whereHas('unit', function($q) {
                $q->where('property_id', $this->propertyFilter);
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate(15);
    }

    public function getPropertiesProperty()
    {
        $user = Auth::user();
        
        if ($user->isOwner()) {
            return \App\Models\Property::where('owner_id', $user->id)->get();
        } elseif ($user->isManager()) {
            return \App\Models\Property::where('manager_id', $user->id)->get();
        }
        
        return collect();
    }
}; ?>

<?php

use function Livewire\Volt\layout;

layout('components.layouts.app', ['title' => __('Tenants')]);
?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Tenants</h1>
            <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">Manage tenant assignments and leases</p>
        </div>
        <flux:link href="{{ route('tenants.create') }}" variant="primary" wire:navigate>
            <flux:icon.plus class="size-4" />
            Assign Tenant
        </flux:link>
    </div>

    <!-- Filters -->
    <div class="flex gap-4">
        <div class="flex-1">
            <flux:input 
                wire:model.live.debounce.300ms="search" 
                placeholder="Search tenants..." 
                type="search"
            />
        </div>
        @if($this->properties->count() > 0)
            <div class="w-48">
                <flux:select wire:model.live="propertyFilter" placeholder="Filter by property">
                    <option value="">All Properties</option>
                    @foreach($this->properties as $property)
                        <option value="{{ $property->id }}">{{ $property->name }}</option>
                    @endforeach
                </flux:select>
            </div>
        @endif
        <div class="w-48">
            <flux:select wire:model.live="statusFilter" placeholder="Filter by status">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="expired">Expired</option>
                <option value="terminated">Terminated</option>
            </flux:select>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if (session()->has('success'))
        <div class="rounded-lg bg-green-50 p-4 text-green-800 dark:bg-green-900/20 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    <!-- Tenants Table -->
    <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-neutral-200 dark:border-neutral-700">
                        <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Tenant</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Unit</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Property</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Monthly Rent</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Lease Start</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Lease End</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Status</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-neutral-600 dark:text-neutral-400">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->tenants as $tenant)
                        <tr class="border-b border-neutral-100 dark:border-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-900/50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-neutral-900 dark:text-neutral-100">{{ $tenant->user->name }}</div>
                                <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ $tenant->user->email }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                                {{ $tenant->unit->unit_number }}
                            </td>
                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                                {{ $tenant->unit->property->name }}
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                ₦{{ number_format($tenant->monthly_rent, 2) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                                {{ $tenant->lease_start_date->format('M d, Y') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                                {{ $tenant->lease_end_date ? $tenant->lease_end_date->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-1 text-xs font-medium
                                    {{ $tenant->lease_status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                    {{ $tenant->lease_status === 'expired' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                    {{ $tenant->lease_status === 'terminated' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                                ">
                                    {{ ucfirst($tenant->lease_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <flux:link href="{{ route('tenants.show', $tenant) }}" wire:navigate>
                                        <flux:icon.eye class="size-4" />
                                    </flux:link>
                                    <flux:link href="{{ route('tenants.edit', $tenant) }}" wire:navigate>
                                        <flux:icon.pencil class="size-4" />
                                    </flux:link>
                                    @if($tenant->lease_status === 'active')
                                        <flux:button 
                                            variant="ghost" 
                                            wire:click="terminateLease({{ $tenant->id }})"
                                            wire:confirm="Are you sure you want to terminate this lease?"
                                        >
                                            <flux:icon.x-mark class="size-4 text-red-600" />
                                        </flux:button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-neutral-600 dark:text-neutral-400">
                                No tenants found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="border-t border-neutral-200 px-4 py-3 dark:border-neutral-700">
            {{ $this->tenants->links() }}
        </div>
    </div>
</div>

