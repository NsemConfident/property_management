<?php

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

use function Livewire\Volt\layout;

layout('components.layouts.app', ['title' => __('Record Payment')]);

new class extends Component {
    public $tenant_id = '';
    public $invoice_id = '';
    public $amount = 0;
    public $payment_date;
    public $payment_method = '';
    public $transaction_reference = '';
    public $notes = '';

    public function mount($invoice = null): void
    {
        $user = Auth::user();
        
        if (!$user->isOwner() && !$user->isManager() && !$user->isTenant()) {
            abort(403, 'Unauthorized access.');
        }

        $this->payment_date = now()->format('Y-m-d');

        if ($invoice) {
            $invoiceModel = Invoice::findOrFail($invoice);
            
            // Check authorization
            if ($user->isTenant() && $user->tenant && $user->tenant->id !== $invoiceModel->tenant_id) {
                abort(403, 'Unauthorized.');
            }
            
            $this->invoice_id = $invoiceModel->id;
            $this->tenant_id = $invoiceModel->tenant_id;
            $this->amount = $invoiceModel->balance;
        } elseif ($user->isTenant() && $user->tenant) {
            $this->tenant_id = $user->tenant->id;
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'invoice_id' => ['nullable', 'exists:invoices,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['nullable', 'in:bank_transfer,card,mobile_money,cash,other'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $tenant = Tenant::findOrFail($this->tenant_id);
        
        // Check authorization
        $user = Auth::user();
        if ($user->isTenant() && $user->tenant->id !== $tenant->id) {
            abort(403, 'Unauthorized.');
        }

        // Generate receipt number
        $receiptNumber = 'RCP-' . now()->format('Ymd') . '-' . str_pad(Payment::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

        $payment = Payment::create([
            'tenant_id' => $this->tenant_id,
            'invoice_id' => $this->invoice_id ?: null,
            'amount' => $this->amount,
            'payment_date' => $this->payment_date,
            'payment_method' => $this->payment_method ?: null,
            'status' => 'completed',
            'transaction_reference' => $this->transaction_reference ?: null,
            'receipt_number' => $receiptNumber,
            'notes' => $this->notes,
        ]);

        // Update invoice if linked
        if ($this->invoice_id) {
            $invoice = Invoice::findOrFail($this->invoice_id);
            $service = app(\App\Services\InvoiceService::class);
            $service->recordPayment($invoice, $this->amount);
        }

        session()->flash('success', 'Payment recorded successfully. Receipt Number: ' . $receiptNumber);
        $this->redirect(route('payments.index'), navigate: true);
    }

    public function getTenantsProperty()
    {
        $user = Auth::user();
        
        if ($user->isTenant() && $user->tenant) {
            return collect([$user->tenant]);
        }

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

    public function getInvoicesProperty()
    {
        if (!$this->tenant_id) {
            return collect();
        }

        return Invoice::where('tenant_id', $this->tenant_id)
            ->where('status', '!=', 'paid')
            ->orderBy('due_date', 'asc')
            ->get();
    }

    public function updatedTenantId(): void
    {
        $this->invoice_id = '';
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
        <!-- Header -->
        <div>
            <flux:link href="{{ route('payments.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-2 text-sm text-neutral-600 dark:text-neutral-400">
                <flux:icon.arrow-left class="size-4" />
                Back to Payments
            </flux:link>
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Record Payment</h1>
            <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">Record a new payment</p>
        </div>

        <!-- Form -->
        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <form wire:submit="save" class="space-y-6">
                <flux:select wire:model.live="tenant_id" label="Tenant" required>
                    <option value="">Select Tenant</option>
                    @foreach($this->tenants as $tenant)
                        <option value="{{ $tenant->id }}">
                            {{ $tenant->user->name }} - {{ $tenant->unit->unit_number ?? 'N/A' }}
                        </option>
                    @endforeach
                </flux:select>

                @if($this->invoices->count() > 0)
                    <flux:select wire:model="invoice_id" label="Link to Invoice (Optional)">
                        <option value="">No Invoice</option>
                        @foreach($this->invoices as $invoice)
                            <option value="{{ $invoice->id }}">
                                {{ $invoice->invoice_number }} - Due: {{ $invoice->due_date->format('M d, Y') }} - Balance: ₦{{ number_format($invoice->balance, 2) }}
                            </option>
                        @endforeach
                    </flux:select>
                @endif

                <div class="grid gap-6 md:grid-cols-2">
                    <flux:input 
                        wire:model.number="amount" 
                        label="Amount (₦)" 
                        type="number"
                        step="0.01"
                        min="0.01"
                        required
                    />

                    <flux:input 
                        wire:model="payment_date" 
                        label="Payment Date" 
                        type="date"
                        required
                    />
                </div>

                <flux:select wire:model="payment_method" label="Payment Method">
                    <option value="">Select Method</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="card">Credit/Debit Card</option>
                    <option value="mobile_money">Mobile Money</option>
                    <option value="cash">Cash</option>
                    <option value="other">Other</option>
                </flux:select>

                <flux:input 
                    wire:model="transaction_reference" 
                    label="Transaction Reference" 
                    placeholder="e.g., TXN-123456"
                />

                <flux:textarea 
                    wire:model="notes" 
                    label="Notes" 
                    placeholder="Additional notes..."
                    rows="3"
                />

                <div class="flex items-center gap-4">
                    <flux:button variant="primary" type="submit">
                        Record Payment
                    </flux:button>
                    <flux:link href="{{ route('payments.index') }}" wire:navigate>
                        Cancel
                    </flux:link>
                </div>
            </form>
        </div>
    </div>
</div>

