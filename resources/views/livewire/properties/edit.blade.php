<?php

use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public Property $property;
    public string $name = '';
    public string $description = '';
    public string $address = '';
    public string $city = '';
    public string $state = '';
    public string $country = 'Nigeria';
    public string $postal_code = '';
    public string $type = 'apartment';
    public int $total_units = 0;
    public string $status = 'active';
    public $manager_id = null;

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

        $this->property = $property;
        $this->name = $property->name;
        $this->description = $property->description ?? '';
        $this->address = $property->address;
        $this->city = $property->city;
        $this->state = $property->state;
        $this->country = $property->country;
        $this->postal_code = $property->postal_code ?? '';
        $this->type = $property->type;
        $this->total_units = $property->total_units;
        $this->status = $property->status;
        $this->manager_id = $property->manager_id;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'type' => ['required', 'in:apartment,house,commercial,other'],
            'total_units' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive,maintenance'],
            'manager_id' => ['nullable', 'exists:users,id'],
        ]);

        $this->property->update($validated);

        session()->flash('success', 'Property updated successfully.');
        $this->redirect(route('properties.index'), navigate: true);
    }

    public function getManagersProperty()
    {
        return User::where('role', 'manager')->get();
    }
}; ?>

<x-layouts.app :title="__('Edit Property')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <!-- Header -->
        <div>
            <flux:link href="{{ route('properties.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-2 text-sm text-neutral-600 dark:text-neutral-400">
                <flux:icon.arrow-left class="size-4" />
                Back to Properties
            </flux:link>
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Edit Property</h1>
            <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">Update property information</p>
        </div>

        <!-- Form -->
        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <form wire:submit="save" class="space-y-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <flux:input 
                        wire:model="name" 
                        label="Property Name" 
                        placeholder="e.g., Sunset Apartments"
                        required
                    />

                    <flux:select wire:model="type" label="Property Type" required>
                        <option value="apartment">Apartment</option>
                        <option value="house">House</option>
                        <option value="commercial">Commercial</option>
                        <option value="other">Other</option>
                    </flux:select>
                </div>

                <flux:textarea 
                    wire:model="description" 
                    label="Description" 
                    placeholder="Describe the property..."
                    rows="3"
                />

                <div class="grid gap-6 md:grid-cols-2">
                    <flux:input 
                        wire:model="address" 
                        label="Address" 
                        placeholder="Street address"
                        required
                    />

                    <flux:input 
                        wire:model="city" 
                        label="City" 
                        placeholder="e.g., Lagos"
                        required
                    />
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <flux:input 
                        wire:model="state" 
                        label="State" 
                        placeholder="e.g., Lagos"
                        required
                    />

                    <flux:input 
                        wire:model="country" 
                        label="Country" 
                        required
                    />

                    <flux:input 
                        wire:model="postal_code" 
                        label="Postal Code" 
                        placeholder="e.g., 101001"
                    />
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <flux:input 
                        wire:model.number="total_units" 
                        label="Total Units" 
                        type="number"
                        min="0"
                        required
                    />

                    <flux:select wire:model="status" label="Status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="maintenance">Maintenance</option>
                    </flux:select>
                </div>

                @if(auth()->user()->isOwner())
                    <flux:select wire:model="manager_id" label="Property Manager (Optional)">
                        <option value="">Select Manager</option>
                        @foreach($this->managers as $manager)
                            <option value="{{ $manager->id }}">{{ $manager->name }} ({{ $manager->email }})</option>
                        @endforeach
                    </flux:select>
                @endif

                <div class="flex items-center gap-4">
                    <flux:button variant="primary" type="submit">
                        Update Property
                    </flux:button>
                    <flux:link href="{{ route('properties.index') }}" wire:navigate>
                        Cancel
                    </flux:link>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>

