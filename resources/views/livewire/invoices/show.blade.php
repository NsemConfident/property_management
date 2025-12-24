<?php

use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

use function Livewire\Volt\layout;

layout('components.layouts.app', ['title' => __('Invoice Details')]);

new class extends Component {
    public Invoice $invoice;

    public function mount(Invoice $invoice): void
    {
        $user = Auth::user();
        
        // Check authorization
        if ($user->isTenant() && $user->tenant && $user->tenant->id !== $invoice->tenant_id) {
            abort(403, 'Unauthorized.');
        }
        
        if ($user->isOwner()) {
            $propertyIds = \App\Models\Property::where('owner_id', $user->id)->pluck('id');
            $unitIds = \App\Models\Unit::whereIn('property_id', $propertyIds)->pluck('id');
            $tenantIds = \App\Models\Tenant::whereIn('unit_id', $unitIds)->pluck('id');
            
            if (!in_array($invoice->tenant_id, $tenantIds->toArray())) {
                abort(403, 'Unauthorized.');
            }
        }
        
        if ($user->isManager()) {
            $propertyIds = \App\Models\Property::where('manager_id', $user->id)->pluck('id');
            $unitIds = \App\Models\Unit::whereIn('property_id', $propertyIds)->pluck('id');
            $tenantIds = \App\Models\Tenant::whereIn('unit_id', $unitIds)->pluck('id');
            
            if (!in_array($invoice->tenant_id, $tenantIds->toArray())) {
                abort(403, 'Unauthorized.');
            }
        }

        $this->invoice = $invoice->load(['tenant', 'tenant.user', 'tenant.unit', 'tenant.unit.property', 'payments']);
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <flux:link href="{{ route('invoices.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-2 text-sm text-neutral-600 dark:text-neutral-400">
                    <flux:icon.arrow-left class="size-4" />
                    Back to Invoices
                </flux:link>
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Invoice {{ $invoice->invoice_number }}</h1>
                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">{{ $invoice->description }}</p>
            </div>
            <div class="flex gap-2">
                <span class="rounded-full px-3 py-1 text-sm font-medium
                    {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                    {{ $invoice->status === 'sent' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                    {{ $invoice->status === 'overdue' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                    {{ $invoice->status === 'draft' ? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' : '' }}
                ">
                    {{ ucfirst($invoice->status) }}
                </span>
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="grid gap-6 md:grid-cols-3">
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <h2 class="mb-4 text-lg font-semibold text-neutral-900 dark:text-neutral-100">Invoice Information</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Invoice Number</dt>
                        <dd class="mt-1 text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $invoice->invoice_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Invoice Date</dt>
                        <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ $invoice->invoice_date->format('M d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Due Date</dt>
                        <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ $invoice->due_date->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <h2 class="mb-4 text-lg font-semibold text-neutral-900 dark:text-neutral-100">Tenant Information</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Name</dt>
                        <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ $invoice->tenant->user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Email</dt>
                        <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ $invoice->tenant->user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Unit</dt>
                        <dd class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">{{ $invoice->tenant->unit->unit_number ?? 'N/A' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <h2 class="mb-4 text-lg font-semibold text-neutral-900 dark:text-neutral-100">Amount Summary</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Total Amount</dt>
                        <dd class="mt-1 text-lg font-bold text-neutral-900 dark:text-neutral-100">₦{{ number_format($invoice->amount, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Paid Amount</dt>
                        <dd class="mt-1 text-sm text-green-600 dark:text-green-400">₦{{ number_format($invoice->paid_amount, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Balance</dt>
                        <dd class="mt-1 text-lg font-bold {{ $invoice->balance > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                            ₦{{ number_format($invoice->balance, 2) }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Line Items -->
        @if($invoice->line_items && count($invoice->line_items) > 0)
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <h2 class="mb-4 text-lg font-semibold text-neutral-900 dark:text-neutral-100">Line Items</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Description</th>
                                <th class="px-4 py-2 text-right text-sm font-medium text-neutral-600 dark:text-neutral-400">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->line_items as $item)
                                <tr class="border-b border-neutral-100 dark:border-neutral-800">
                                    <td class="px-4 py-2 text-sm text-neutral-900 dark:text-neutral-100">{{ $item['description'] ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 text-right text-sm font-medium text-neutral-900 dark:text-neutral-100">₦{{ number_format($item['amount'] ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="px-4 py-2 text-right text-sm font-bold text-neutral-900 dark:text-neutral-100">Total</td>
                                <td class="px-4 py-2 text-right text-sm font-bold text-neutral-900 dark:text-neutral-100">₦{{ number_format($invoice->amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif

        <!-- Payments -->
        @if($invoice->payments->count() > 0)
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <h2 class="mb-4 text-lg font-semibold text-neutral-900 dark:text-neutral-100">Payments</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Date</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Amount</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Method</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Reference</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->payments as $payment)
                                <tr class="border-b border-neutral-100 dark:border-neutral-800">
                                    <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $payment->payment_date->format('M d, Y') }}</td>
                                    <td class="px-4 py-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">₦{{ number_format($payment->amount, 2) }}</td>
                                    <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'N/A')) }}</td>
                                    <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $payment->transaction_reference ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        <span class="rounded-full px-2 py-1 text-xs
                                            {{ $payment->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                        ">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>

