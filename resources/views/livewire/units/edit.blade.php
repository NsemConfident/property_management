<?php

use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

use function Livewire\Volt\layout;

layout('components.layouts.app', ['title' => __('Edit Unit')]);

new class extends Component {
    public Unit $unit;
    public string $unit_number = '';
    public string $unit_type = '';
    public $monthly_rent = 0;
    public $deposit = 0;
    public int $bedrooms = 0;
    public int $bathrooms = 0;
    public $square_feet = null;
    public string $status = 'available';
    public string $description = '';

    public function mount(Unit $unit): void
    {
        $user = Auth::user();
        
        if (!$user->isOwner() && !$user->isManager()) {
            abort(403, 'Unauthorized access.');
        }

        // Check authorization
        if ($user->isOwner() && $unit->property->owner_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }
        if ($user->isManager() && $unit->property->manager_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $this->unit = $unit;
        $this->unit_number = $unit->unit_number;
        $this->unit_type = $unit->unit_type ?? '';
        $this->monthly_rent = $unit->monthly_rent;
        $this->deposit = $unit->deposit;
        $this->bedrooms = $unit->bedrooms;
        $this->bathrooms = $unit->bathrooms;
        $this->square_feet = $unit->square_feet;
        $this->status = $unit->status;
        $this->description = $unit->description ?? '';
    }

    public function save(): void
    {
        $validated = $this->validate([
            'unit_number' => ['required', 'string', 'max:255'],
            'unit_type' => ['nullable', 'string', 'max:255'],
            'monthly_rent' => ['required', 'numeric', 'min:0'],
            'deposit' => ['required', 'numeric', 'min:0'],
            'bedrooms' => ['required', 'integer', 'min:0'],
            'bathrooms' => ['required', 'integer', 'min:0'],
            'square_feet' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:available,occupied,maintenance,reserved'],
            'description' => ['nullable', 'string'],
        ]);

        // Check if unit number already exists for this property (excluding current unit)
        $exists = Unit::where('property_id', $this->unit->property_id)
            ->where('unit_number', $this->unit_number)
            ->where('id', '!=', $this->unit->id)
            ->exists();

        if ($exists) {
            $this->addError('unit_number', 'Unit number already exists for this property.');
            return;
        }

        $this->unit->update($validated);

        session()->flash('success', 'Unit updated successfully.');
        $this->redirect(route('units.show', $this->unit), navigate: true);
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
        <!-- Header -->
        <div>
            <flux:link href="{{ route('units.show', $unit) }}" wire:navigate class="mb-4 inline-flex items-center gap-2 text-sm text-neutral-600 dark:text-neutral-400">
                <flux:icon.arrow-left class="size-4" />
                Back to Unit
            </flux:link>
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Edit Unit</h1>
            <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">Update unit information</p>
        </div>

        <!-- Form -->
        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <form wire:submit="save" class="space-y-6">
                <div class="rounded-lg bg-neutral-50 p-4 dark:bg-neutral-900">
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Property</p>
                    <p class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">{{ $unit->property->name }}</p>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <flux:input 
                        wire:model="unit_number" 
                        label="Unit Number" 
                        placeholder="e.g., A101"
                        required
                    />

                    <flux:input 
                        wire:model="unit_type" 
                        label="Unit Type" 
                        placeholder="e.g., 2-bedroom, Studio"
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

                    <flux:input 
                        wire:model.number="deposit" 
                        label="Deposit (₦)" 
                        type="number"
                        step="0.01"
                        min="0"
                        required
                    />
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <flux:input 
                        wire:model.number="bedrooms" 
                        label="Bedrooms" 
                        type="number"
                        min="0"
                        required
                    />

                    <flux:input 
                        wire:model.number="bathrooms" 
                        label="Bathrooms" 
                        type="number"
                        min="0"
                        required
                    />

                    <flux:input 
                        wire:model.number="square_feet" 
                        label="Square Feet" 
                        type="number"
                        step="0.01"
                        min="0"
                    />
                </div>

                <flux:select wire:model="status" label="Status" required>
                    <option value="available">Available</option>
                    <option value="occupied">Occupied</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="reserved">Reserved</option>
                </flux:select>

                <flux:textarea 
                    wire:model="description" 
                    label="Description" 
                    placeholder="Describe the unit..."
                    rows="3"
                />

                <div class="flex items-center gap-4">
                    <flux:button variant="primary" type="submit">
                        Update Unit
                    </flux:button>
                    <flux:link href="{{ route('units.show', $unit) }}" wire:navigate>
                        Cancel
                    </flux:link>
                </div>
            </form>
        </div>
    </div>
</div>

