<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public Tenant $tenant;

    public function mount(Tenant $tenant): void
    {
        $user = Auth::user();
        
        // Check authorization
        if ($user->isOwner()) {
            $propertyIds = \App\Models\Property::where('owner_id', $user->id)->pluck('id');
            $unitIds = \App\Models\Unit::whereIn('property_id', $propertyIds)->pluck('id');
            if (!in_array($tenant->unit_id, $unitIds->toArray())) {
                abort(403, 'Unauthorized.');
            }
        } elseif ($user->isManager()) {
            $propertyIds = \App\Models\Property::where('manager_id', $user->id)->pluck('id');
            $unitIds = \App\Models\Unit::whereIn('property_id', $propertyIds)->pluck('id');
            if (!in_array($tenant->unit_id, $unitIds->toArray())) {
                abort(403, 'Unauthorized.');
            }
        } elseif ($user->isTenant() && $user->tenant && $user->tenant->id !== $tenant->id) {
            abort(403, 'Unauthorized.');
        }

        $this->tenant = $tenant->load(['user', 'unit', 'unit.property', 'invoices', 'payments']);
    }
}; ?>

<?php

use function Livewire\Volt\layout;

layout('components.layouts.app', ['title' => __('Tenant Details')]);
?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <flux:link href="{{ route('tenants.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-2 text-sm text-neutral-600 dark:text-neutral-400">
                <flux:icon.arrow-left class="size-4" />
                Back to Tenants
            </flux:link>
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ $tenant->user->name }}</h1>
            <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">Tenant Information & Lease Details</p>
        </div>
        @if(auth()->user()->isOwner() || auth()->user()->isManager())
            <flux:link href="{{ route('tenants.edit', $tenant) }}" variant="primary" wire:navigate>
                <flux:icon.pencil class="size-4" />
                Edit Tenant
            </flux:link>
        @endif
    </div>

    <!-- Tenant Information -->
    <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <h2 class="mb-4 text-lg font-semibold text-neutral-900 dark:text-neutral-100">Tenant Information</h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Name</dt>
                    <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ $tenant->user->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Email</dt>
                    <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ $tenant->user->email }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Phone</dt>
                    <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ $tenant->user->phone ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Address</dt>
                    <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ $tenant->user->address ?? 'N/A' }}</dd>
                </div>
                @if($tenant->emergency_contact_name)
                    <div>
                        <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Emergency Contact</dt>
                        <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">
                            {{ $tenant->emergency_contact_name }}
                            @if($tenant->emergency_contact_phone)
                                <br><span class="text-neutral-600 dark:text-neutral-400">{{ $tenant->emergency_contact_phone }}</span>
                            @endif
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <h2 class="mb-4 text-lg font-semibold text-neutral-900 dark:text-neutral-100">Lease Information</h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Property</dt>
                    <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ $tenant->unit->property->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Unit</dt>
                    <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ $tenant->unit->unit_number }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Monthly Rent</dt>
                    <dd class="mt-1 text-lg font-bold text-neutral-900 dark:text-neutral-100">₦{{ number_format($tenant->monthly_rent, 2) }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Lease Start Date</dt>
                    <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ $tenant->lease_start_date->format('M d, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Lease End Date</dt>
                    <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ $tenant->lease_end_date ? $tenant->lease_end_date->format('M d, Y') : 'Open-ended' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Lease Status</dt>
                    <dd class="mt-1">
                        <span class="rounded-full px-2 py-1 text-xs font-medium
                            {{ $tenant->lease_status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                            {{ $tenant->lease_status === 'expired' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                            {{ $tenant->lease_status === 'terminated' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                        ">
                            {{ ucfirst($tenant->lease_status) }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    @if($tenant->notes)
        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <h2 class="mb-2 text-lg font-semibold text-neutral-900 dark:text-neutral-100">Notes</h2>
            <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ $tenant->notes }}</p>
        </div>
    @endif

    <!-- Invoices & Payments Summary -->
    <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <h2 class="mb-4 text-lg font-semibold text-neutral-900 dark:text-neutral-100">Invoices</h2>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">Total Invoices</span>
                    <span class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $tenant->invoices->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">Paid</span>
                    <span class="text-sm font-medium text-green-600 dark:text-green-400">{{ $tenant->invoices->where('status', 'paid')->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">Overdue</span>
                    <span class="text-sm font-medium text-red-600 dark:text-red-400">{{ $tenant->invoices->where('status', 'overdue')->count() }}</span>
                </div>
            </div>
            <div class="mt-4">
                <flux:link href="{{ route('invoices.index', ['tenantFilter' => $tenant->id]) }}" wire:navigate>
                    View All Invoices →
                </flux:link>
            </div>
        </div>

        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <h2 class="mb-4 text-lg font-semibold text-neutral-900 dark:text-neutral-100">Payments</h2>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">Total Payments</span>
                    <span class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $tenant->payments->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">Total Paid</span>
                    <span class="text-sm font-medium text-green-600 dark:text-green-400">₦{{ number_format($tenant->payments->where('status', 'completed')->sum('amount'), 2) }}</span>
                </div>
            </div>
            <div class="mt-4">
                <flux:link href="{{ route('payments.index', ['tenantFilter' => $tenant->id]) }}" wire:navigate>
                    View All Payments →
                </flux:link>
            </div>
        </div>
    </div>
</div>

