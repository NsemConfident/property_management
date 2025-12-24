<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public Tenant $tenant;
    public $lease_start_date;
    public $lease_end_date = '';
    public $monthly_rent = 0;
    public $lease_status = 'active';
    public $emergency_contact_name = '';
    public $emergency_contact_phone = '';
    public $notes = '';

    public function mount(Tenant $tenant): void
    {
        $user = Auth::user();
        
        if (!$user->isOwner() && !$user->isManager()) {
            abort(403, 'Unauthorized access.');
        }

        // Check authorization
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

        $this->tenant = $tenant;
        $this->lease_start_date = $tenant->lease_start_date->format('Y-m-d');
        $this->lease_end_date = $tenant->lease_end_date ? $tenant->lease_end_date->format('Y-m-d') : '';
        $this->monthly_rent = $tenant->monthly_rent;
        $this->lease_status = $tenant->lease_status;
        $this->emergency_contact_name = $tenant->emergency_contact_name ?? '';
        $this->emergency_contact_phone = $tenant->emergency_contact_phone ?? '';
        $this->notes = $tenant->notes ?? '';
    }

    public function save(): void
    {
        $validated = $this->validate([
            'lease_start_date' => ['required', 'date'],
            'lease_end_date' => ['nullable', 'date', 'after:lease_start_date'],
            'monthly_rent' => ['required', 'numeric', 'min:0'],
            'lease_status' => ['required', 'in:active,expired,terminated'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->tenant->update([
            'lease_start_date' => $this->lease_start_date,
            'lease_end_date' => $this->lease_end_date ?: null,
            'monthly_rent' => $this->monthly_rent,
            'lease_status' => $this->lease_status,
            'emergency_contact_name' => $this->emergency_contact_name ?: null,
            'emergency_contact_phone' => $this->emergency_contact_phone ?: null,
            'notes' => $this->notes ?: null,
        ]);

        // Update unit status based on lease status
        if ($this->lease_status === 'terminated' || $this->lease_status === 'expired') {
            $this->tenant->unit->update(['status' => 'available']);
        } elseif ($this->lease_status === 'active') {
            $this->tenant->unit->update(['status' => 'occupied']);
        }

        session()->flash('success', 'Tenant information updated successfully.');
        $this->redirect(route('tenants.show', $this->tenant), navigate: true);
    }
}; ?>

<?php

use function Livewire\Volt\layout;

layout('components.layouts.app', ['title' => __('Edit Tenant')]);
?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <!-- Header -->
    <div>
        <flux:link href="{{ route('tenants.show', $tenant) }}" wire:navigate class="mb-4 inline-flex items-center gap-2 text-sm text-neutral-600 dark:text-neutral-400">
            <flux:icon.arrow-left class="size-4" />
            Back to Tenant
        </flux:link>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Edit Tenant</h1>
        <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">Update tenant and lease information</p>
    </div>

    <!-- Form -->
    <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
        <div class="mb-6 rounded-lg bg-neutral-50 p-4 dark:bg-neutral-900">
            <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Tenant</p>
            <p class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">{{ $tenant->user->name }}</p>
            <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ $tenant->unit->property->name }} - Unit {{ $tenant->unit->unit_number }}</p>
        </div>

        <form wire:submit="save" class="space-y-6">
            <div class="grid gap-6 md:grid-cols-2">
                <flux:input 
                    wire:model="lease_start_date" 
                    label="Lease Start Date" 
                    type="date"
                    required
                />

                <flux:input 
                    wire:model="lease_end_date" 
                    label="Lease End Date (Optional)" 
                    type="date"
                />
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <flux:input 
                    wire:model.number="monthly_rent" 
                    label="Monthly Rent (₦)" 
                    type="number"
                    step="0.01"
                    min="0"
                    required
                />

                <flux:select wire:model="lease_status" label="Lease Status" required>
                    <option value="active">Active</option>
                    <option value="expired">Expired</option>
                    <option value="terminated">Terminated</option>
                </flux:select>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <flux:input 
                    wire:model="emergency_contact_name" 
                    label="Emergency Contact Name" 
                    placeholder="Full name"
                />

                <flux:input 
                    wire:model="emergency_contact_phone" 
                    label="Emergency Contact Phone" 
                    placeholder="+234..."
                />
            </div>

            <flux:textarea 
                wire:model="notes" 
                label="Notes" 
                placeholder="Additional notes about the tenant or lease..."
                rows="3"
            />

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">
                    Update Tenant
                </flux:button>
                <flux:link href="{{ route('tenants.show', $tenant) }}" wire:navigate>
                    Cancel
                </flux:link>
            </div>
        </form>
    </div>
</div>

