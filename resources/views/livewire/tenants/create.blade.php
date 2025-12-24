<?php

use App\Models\Tenant;
use App\Models\User;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public $user_id = '';
    public $unit_id = '';
    public $lease_start_date;
    public $lease_end_date = '';
    public $monthly_rent = 0;
    public $emergency_contact_name = '';
    public $emergency_contact_phone = '';
    public $notes = '';

    public function mount($unit = null): void
    {
        $user = Auth::user();
        
        if (!$user->isOwner() && !$user->isManager()) {
            abort(403, 'Unauthorized access.');
        }

        $this->lease_start_date = now()->format('Y-m-d');

        if ($unit) {
            $unitModel = Unit::findOrFail($unit);
            
            // Check authorization
            if ($user->isOwner() && $unitModel->property->owner_id !== $user->id) {
                abort(403, 'Unauthorized.');
            }
            if ($user->isManager() && $unitModel->property->manager_id !== $user->id) {
                abort(403, 'Unauthorized.');
            }

            $this->unit_id = $unitModel->id;
            $this->monthly_rent = $unitModel->monthly_rent;
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'user_id' => ['required', 'exists:users,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'lease_start_date' => ['required', 'date'],
            'lease_end_date' => ['nullable', 'date', 'after:lease_start_date'],
            'monthly_rent' => ['required', 'numeric', 'min:0'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        // Check if user is already a tenant
        $existingTenant = Tenant::where('user_id', $this->user_id)
            ->where('lease_status', 'active')
            ->first();

        if ($existingTenant) {
            $this->addError('user_id', 'This user is already an active tenant in another unit.');
            return;
        }

        // Check if unit is available
        $unit = Unit::findOrFail($this->unit_id);
        if ($unit->status !== 'available' && $unit->status !== 'reserved') {
            $this->addError('unit_id', 'This unit is not available for assignment.');
            return;
        }

        // Check authorization
        $user = Auth::user();
        if ($user->isOwner() && $unit->property->owner_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }
        if ($user->isManager() && $unit->property->manager_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $tenant = Tenant::create([
            'user_id' => $this->user_id,
            'unit_id' => $this->unit_id,
            'lease_start_date' => $this->lease_start_date,
            'lease_end_date' => $this->lease_end_date ?: null,
            'monthly_rent' => $this->monthly_rent,
            'lease_status' => 'active',
            'emergency_contact_name' => $this->emergency_contact_name ?: null,
            'emergency_contact_phone' => $this->emergency_contact_phone ?: null,
            'notes' => $this->notes ?: null,
        ]);

        // Update unit status to occupied
        $unit->update(['status' => 'occupied']);

        // Update user role to tenant if not already
        $userModel = User::findOrFail($this->user_id);
        if ($userModel->role !== 'tenant') {
            $userModel->update(['role' => 'tenant']);
        }

        session()->flash('success', 'Tenant assigned successfully.');
        $this->redirect(route('tenants.show', $tenant), navigate: true);
    }

    public function getUsersProperty()
    {
        // Get users who are not already active tenants
        $activeTenantUserIds = Tenant::where('lease_status', 'active')->pluck('user_id');
        
        return User::whereNotIn('id', $activeTenantUserIds)
            ->orderBy('name')
            ->get();
    }

    public function getUnitsProperty()
    {
        $user = Auth::user();
        
        if ($user->isOwner()) {
            $propertyIds = \App\Models\Property::where('owner_id', $user->id)->pluck('id');
            return Unit::whereIn('property_id', $propertyIds)
                ->whereIn('status', ['available', 'reserved'])
                ->with('property')
                ->get();
        } elseif ($user->isManager()) {
            $propertyIds = \App\Models\Property::where('manager_id', $user->id)->pluck('id');
            return Unit::whereIn('property_id', $propertyIds)
                ->whereIn('status', ['available', 'reserved'])
                ->with('property')
                ->get();
        }
        
        return collect();
    }

    public function updatedUnitId(): void
    {
        if ($this->unit_id) {
            $unit = Unit::find($this->unit_id);
            if ($unit) {
                $this->monthly_rent = $unit->monthly_rent;
            }
        }
    }
}; ?>

<?php

use function Livewire\Volt\layout;

layout('components.layouts.app', ['title' => __('Assign Tenant')]);
?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <!-- Header -->
    <div>
        <flux:link href="{{ route('tenants.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-2 text-sm text-neutral-600 dark:text-neutral-400">
            <flux:icon.arrow-left class="size-4" />
            Back to Tenants
        </flux:link>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Assign Tenant to Unit</h1>
        <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">Link a user to a unit and create a lease agreement</p>
    </div>

    <!-- Form -->
    <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
        <form wire:submit="save" class="space-y-6">
            <flux:select wire:model.live="user_id" label="User" required>
                <option value="">Select User</option>
                @foreach($this->users as $user)
                    <option value="{{ $user->id }}">
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="unit_id" label="Unit" required>
                <option value="">Select Unit</option>
                @foreach($this->units as $unit)
                    <option value="{{ $unit->id }}">
                        {{ $unit->property->name }} - Unit {{ $unit->unit_number }} ({{ ucfirst($unit->status) }})
                    </option>
                @endforeach
            </flux:select>

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

            <flux:input 
                wire:model.number="monthly_rent" 
                label="Monthly Rent (₦)" 
                type="number"
                step="0.01"
                min="0"
                required
            />

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
                    Assign Tenant
                </flux:button>
                <flux:link href="{{ route('tenants.index') }}" wire:navigate>
                    Cancel
                </flux:link>
            </div>
        </form>
    </div>
</div>

