<?php

use App\Models\Tenant;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

use function Livewire\Volt\layout;

layout('components.layouts.app');

new class extends Component {
    public $tenant_id = '';
    public $invoice_date;
    public $due_date;
    public $amount = 0;
    public $description = '';
    public $line_items = [];

    public function mount(): void
    {
        $user = Auth::user();
        
        if (!$user->isOwner() && !$user->isManager()) {
            abort(403, 'Unauthorized access.');
        }

        $this->invoice_date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(7)->format('Y-m-d');
    }

    public function save(): void
    {
        $validated = $this->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:invoice_date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['required', 'string'],
        ]);

        $tenant = Tenant::findOrFail($this->tenant_id);

        $invoice = \App\Models\Invoice::create([
            'tenant_id' => $tenant->id,
            'invoice_number' => \App\Models\Invoice::generateInvoiceNumber(),
            'invoice_date' => $this->invoice_date,
            'due_date' => $this->due_date,
            'amount' => $this->amount,
            'paid_amount' => 0,
            'balance' => $this->amount,
            'status' => 'draft',
            'description' => $this->description,
            'line_items' => [
                [
                    'description' => $this->description,
                    'amount' => $this->amount,
                ],
            ],
        ]);

        session()->flash('success', 'Invoice created successfully.');
        $this->redirect(route('invoices.show', $invoice), navigate: true);
    }

    public function generateMonthlyInvoice(): void
    {
        $this->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
        ]);

        $tenant = Tenant::findOrFail($this->tenant_id);
        $service = new InvoiceService();
        
        $invoice = $service->generateMonthlyInvoice($tenant);
        $service->markInvoiceAsSent($invoice);

        session()->flash('success', 'Monthly invoice generated and sent successfully.');
        $this->redirect(route('invoices.show', $invoice), navigate: true);
    }

    public function getTenantsProperty()
    {
        $user = Auth::user();
        
        if ($user->isOwner()) {
            $tenantIds = Tenant::whereIn('unit_id', function($q) use ($user) {
                $q->select('id')
                  ->from('units')
                  ->whereIn('property_id', function($q2) use ($user) {
                      $q2->select('id')
                         ->from('properties')
                         ->where('owner_id', $user->id);
                  });
            })->pluck('id');
            
            return Tenant::whereIn('id', $tenantIds)
                ->where('lease_status', 'active')
                ->with('user', 'unit')
                ->get();
        }

        if ($user->isManager()) {
            $tenantIds = Tenant::whereIn('unit_id', function($q) use ($user) {
                $q->select('id')
                  ->from('units')
                  ->whereIn('property_id', function($q2) use ($user) {
                      $q2->select('id')
                         ->from('properties')
                         ->where('manager_id', $user->id);
                  });
            })->pluck('id');
            
            return Tenant::whereIn('id', $tenantIds)
                ->where('lease_status', 'active')
                ->with('user', 'unit')
                ->get();
        }

        return collect(); 
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
        <!-- Header -->
        <div>
            <flux:link href="{{ route('invoices.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-2 text-sm text-neutral-600 dark:text-neutral-400">
                <flux:icon.arrow-left class="size-4" />
                Back to Invoices
            </flux:link>
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Create Invoice</h1>
            <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">Generate a new invoice for a tenant</p>
        </div>

        <!-- Quick Generate Monthly Invoice -->
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-6 dark:border-blue-800 dark:bg-blue-900/20">
            <h2 class="mb-2 text-lg font-semibold text-neutral-900 dark:text-neutral-100">Quick Generate Monthly Invoice</h2>
            <p class="mb-4 text-sm text-neutral-600 dark:text-neutral-400">Generate a monthly rent invoice for a tenant automatically</p>
            <form wire:submit="generateMonthlyInvoice" class="flex gap-4">
                <div class="flex-1">
                    <flux:select wire:model="tenant_id" label="Select Tenant" required>
                        <option value="">Choose a tenant</option>
                        @foreach($this->tenants as $tenant)
                            <option value="{{ $tenant->id }}">
                                {{ $tenant->user->name }} - {{ $tenant->unit->unit_number ?? 'N/A' }} (₦{{ number_format($tenant->monthly_rent, 2) }}/month)
                            </option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="flex items-end">
                    <flux:button variant="primary" type="submit">
                        Generate Monthly Invoice
                    </flux:button>
                </div>
            </form>
        </div>

        <!-- Manual Invoice Form -->
        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <h2 class="mb-4 text-lg font-semibold text-neutral-900 dark:text-neutral-100">Create Custom Invoice</h2>
            <form wire:submit="save" class="space-y-6">
                <flux:select wire:model="tenant_id" label="Tenant" required>
                    <option value="">Select Tenant</option>
                    @foreach($this->tenants as $tenant)
                        <option value="{{ $tenant->id }}">
                            {{ $tenant->user->name }} - {{ $tenant->unit->unit_number ?? 'N/A' }}
                        </option>
                    @endforeach
                </flux:select>

                <div class="grid gap-6 md:grid-cols-2">
                    <flux:input 
                        wire:model="invoice_date" 
                        label="Invoice Date" 
                        type="date"
                        required
                    />

                    <flux:input 
                        wire:model="due_date" 
                        label="Due Date" 
                        type="date"
                        required
                    />
                </div>

                <flux:input 
                    wire:model.number="amount" 
                    label="Amount (₦)" 
                    type="number"
                    step="0.01"
                    min="0"
                    required
                />

                <flux:textarea 
                    wire:model="description" 
                    label="Description" 
                    placeholder="Invoice description..."
                    rows="3"
                    required
                />

                <div class="flex items-center gap-4">
                    <flux:button variant="primary" type="submit">
                        Create Invoice
                    </flux:button>
                    <flux:link href="{{ route('invoices.index') }}" wire:navigate>
                        Cancel
                    </flux:link>
                </div>
            </form>
        </div>
    </div>
</div>

