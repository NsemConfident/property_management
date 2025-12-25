<?php

use App\Models\Tenant;

use function Livewire\Volt\layout;

layout('components.layouts.app', ['title' => __('Tenant Dashboard')]);
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public $tenant;
    public $upcomingInvoices;
    public $recentPayments;
    public $overdueInvoices;
    public $totalPaid;
    public $totalDue;

    public function mount(): void
    {
        $user = Auth::user();
        
        if (!$user->isTenant()) {
            abort(403, 'Unauthorized access.');
        }

        $this->tenant = $user->tenant;
        
        if ($this->tenant) {
            $this->loadData();
        }
    }

    public function loadData(): void
    {
        $this->upcomingInvoices = Invoice::where('tenant_id', $this->tenant->id)
            ->where('status', '!=', 'paid')
            ->where('due_date', '>=', now())
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get();

        $this->overdueInvoices = Invoice::where('tenant_id', $this->tenant->id)
            ->where('status', 'overdue')
            ->orWhere(function($query) {
                $query->where('tenant_id', $this->tenant->id)
                    ->where('due_date', '<', now())
                    ->where('status', '!=', 'paid');
            })
            ->orderBy('due_date', 'asc')
            ->get();

        $this->recentPayments = Payment::where('tenant_id', $this->tenant->id)
            ->where('status', 'completed')
            ->orderBy('payment_date', 'desc')
            ->limit(5)
            ->get();

        $this->totalPaid = Payment::where('tenant_id', $this->tenant->id)
            ->where('status', 'completed')
            ->sum('amount');

        $this->totalDue = Invoice::where('tenant_id', $this->tenant->id)
            ->where('status', '!=', 'paid')
            ->sum('balance');
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
        @if($tenant)
            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                    <h3 class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Total Paid</h3>
                    <p class="mt-2 text-3xl font-bold text-neutral-900 dark:text-neutral-100">
                        ₦{{ number_format($totalPaid, 2) }}
                    </p>
                </div>
                <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                    <h3 class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Total Due</h3>
                    <p class="mt-2 text-3xl font-bold text-red-600 dark:text-red-400">
                        ₦{{ number_format($totalDue, 2) }}
                    </p>
                </div>
                <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                    <h3 class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Overdue Invoices</h3>
                    <p class="mt-2 text-3xl font-bold text-red-600 dark:text-red-400">
                        {{ $overdueInvoices->count() }}
                    </p>
                </div>
            </div>

            <!-- Current Unit Info -->
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <h2 class="mb-4 text-xl font-semibold text-neutral-900 dark:text-neutral-100">Current Unit</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Property</p>
                        <p class="font-medium text-neutral-900 dark:text-neutral-100">
                            {{ $tenant->unit->property->name ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Unit Number</p>
                        <p class="font-medium text-neutral-900 dark:text-neutral-100">
                            {{ $tenant->unit->unit_number ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Monthly Rent</p>
                        <p class="font-medium text-neutral-900 dark:text-neutral-100">
                            ₦{{ number_format($tenant->monthly_rent, 2) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Lease Status</p>
                        <p class="font-medium text-neutral-900 dark:text-neutral-100">
                            <span class="rounded-full px-2 py-1 text-xs {{ $tenant->lease_status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                {{ ucfirst($tenant->lease_status) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Upcoming Invoices -->
            @if($upcomingInvoices->count() > 0)
                <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                    <h2 class="mb-4 text-xl font-semibold text-neutral-900 dark:text-neutral-100">Upcoming Invoices</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                    <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Invoice #</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Due Date</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Amount</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Status</th>
                                    <th class="px-4 py-2 text-right text-sm font-medium text-neutral-600 dark:text-neutral-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($upcomingInvoices as $invoice)
                                    <tr class="border-b border-neutral-100 dark:border-neutral-800">
                                        <td class="px-4 py-2 text-sm text-neutral-900 dark:text-neutral-100">{{ $invoice->invoice_number }}</td>
                                        <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $invoice->due_date->format('M d, Y') }}</td>
                                        <td class="px-4 py-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">₦{{ number_format($invoice->amount, 2) }}</td>
                                        <td class="px-4 py-2 text-sm">
                                            <span class="rounded-full px-2 py-1 text-xs {{ $invoice->status === 'sent' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                                {{ ucfirst($invoice->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-sm">
                                            <div class="flex items-center justify-end gap-2">
                                                @if(!$invoice->isPaid())
                                                    <a href="{{ route('payment.initiate', $invoice) }}" class="inline-flex items-center gap-1 rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                                                        <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                                        </svg>
                                                        Pay Now
                                                    </a>
                                                @endif
                                                <flux:link href="{{ route('invoices.show', $invoice) }}" wire:navigate>
                                                    <flux:icon.eye class="size-4" />
                                                </flux:link>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Overdue Invoices -->
            @if($overdueInvoices->count() > 0)
                <div class="rounded-xl border border-red-200 bg-red-50 p-6 dark:border-red-800 dark:bg-red-900/20">
                    <h2 class="mb-4 text-xl font-semibold text-red-900 dark:text-red-100">⚠️ Overdue Invoices</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-red-200 dark:border-red-800">
                                    <th class="px-4 py-2 text-left text-sm font-medium text-red-800 dark:text-red-200">Invoice #</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-red-800 dark:text-red-200">Due Date</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-red-800 dark:text-red-200">Amount</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-red-800 dark:text-red-200">Balance</th>
                                    <th class="px-4 py-2 text-right text-sm font-medium text-red-800 dark:text-red-200">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($overdueInvoices as $invoice)
                                    <tr class="border-b border-red-100 dark:border-red-800">
                                        <td class="px-4 py-2 text-sm font-medium text-red-900 dark:text-red-100">{{ $invoice->invoice_number }}</td>
                                        <td class="px-4 py-2 text-sm text-red-700 dark:text-red-300">{{ $invoice->due_date->format('M d, Y') }}</td>
                                        <td class="px-4 py-2 text-sm font-medium text-red-900 dark:text-red-100">₦{{ number_format($invoice->amount, 2) }}</td>
                                        <td class="px-4 py-2 text-sm font-bold text-red-900 dark:text-red-100">₦{{ number_format($invoice->balance, 2) }}</td>
                                        <td class="px-4 py-2 text-sm">
                                            <div class="flex items-center justify-end gap-2">
                                                @if(!$invoice->isPaid())
                                                    <a href="{{ route('payment.initiate', $invoice) }}" class="inline-flex items-center gap-1 rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                                                        <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                                        </svg>
                                                        Pay Now
                                                    </a>
                                                @endif
                                                <flux:link href="{{ route('invoices.show', $invoice) }}" wire:navigate>
                                                    <flux:icon.eye class="size-4" />
                                                </flux:link>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Recent Payments -->
            @if($recentPayments->count() > 0)
                <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                    <h2 class="mb-4 text-xl font-semibold text-neutral-900 dark:text-neutral-100">Recent Payments</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                    <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Date</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Amount</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Method</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Reference</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPayments as $payment)
                                    <tr class="border-b border-neutral-100 dark:border-neutral-800">
                                        <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $payment->payment_date->format('M d, Y') }}</td>
                                        <td class="px-4 py-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">₦{{ number_format($payment->amount, 2) }}</td>
                                        <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'N/A')) }}</td>
                                        <td class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400">{{ $payment->transaction_reference ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @else
            <div class="flex h-full w-full flex-1 flex-col items-center justify-center rounded-xl border border-neutral-200 bg-white p-12 dark:border-neutral-700 dark:bg-neutral-800">
                <p class="text-lg text-neutral-600 dark:text-neutral-400">No tenant record found. Please contact your property manager.</p>
            </div>
        @endif
    </div>
</div>