<?php

use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $tenantFilter = '';

    public function mount(): void
    {
        $user = Auth::user();
        
        if (!$user->isOwner() && !$user->isManager() && !$user->isTenant()) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function getPaymentsProperty()
    {
        $user = Auth::user();
        
        $query = Payment::query()->with(['tenant', 'tenant.user', 'invoice']);

        if ($user->isTenant() && $user->tenant) {
            $query->where('tenant_id', $user->tenant->id);
        } elseif ($user->isOwner()) {
            $tenantIds = \App\Models\Tenant::whereIn('unit_id', function($q) use ($user) {
                $q->select('id')
                  ->from('units')
                  ->whereIn('property_id', function($q2) use ($user) {
                      $q2->select('id')
                         ->from('properties')
                         ->where('owner_id', $user->id);
                  });
            })->pluck('id');
            
            $query->whereIn('tenant_id', $tenantIds);
        } elseif ($user->isManager()) {
            $tenantIds = \App\Models\Tenant::whereIn('unit_id', function($q) use ($user) {
                $q->select('id')
                  ->from('units')
                  ->whereIn('property_id', function($q2) use ($user) {
                      $q2->select('id')
                         ->from('properties')
                         ->where('manager_id', $user->id);
                  });
            })->pluck('id');
            
            $query->whereIn('tenant_id', $tenantIds);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('transaction_reference', 'like', '%' . $this->search . '%')
                  ->orWhere('receipt_number', 'like', '%' . $this->search . '%')
                  ->orWhereHas('tenant.user', function($q) {
                      $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->tenantFilter) {
            $query->where('tenant_id', $this->tenantFilter);
        }

        return $query->orderBy('payment_date', 'desc')
            ->paginate(15);
    }

    public function getTenantsProperty()
    {
        $user = Auth::user();
        
        if ($user->isTenant()) {
            return collect();
        }

        if ($user->isOwner()) {
            $tenantIds = \App\Models\Tenant::whereIn('unit_id', function($q) use ($user) {
                $q->select('id')
                  ->from('units')
                  ->whereIn('property_id', function($q2) use ($user) {
                      $q2->select('id')
                         ->from('properties')
                         ->where('owner_id', $user->id);
                  });
            })->pluck('id');
            
            return \App\Models\Tenant::whereIn('id', $tenantIds)->with('user')->get();
        }

        if ($user->isManager()) {
            $tenantIds = \App\Models\Tenant::whereIn('unit_id', function($q) use ($user) {
                $q->select('id')
                  ->from('units')
                  ->whereIn('property_id', function($q2) use ($user) {
                      $q2->select('id')
                         ->from('properties')
                         ->where('manager_id', $user->id);
                  });
            })->pluck('id');
            
            return \App\Models\Tenant::whereIn('id', $tenantIds)->with('user')->get();
        }

        return collect();
    }
}; ?>

<x-layouts.app :title="__('Payments')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Payments</h1>
                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">View and manage payment records</p>
            </div>
            <flux:link href="{{ route('payments.create') }}" variant="primary" wire:navigate>
                <flux:icon.plus class="size-4" />
                Record Payment
            </flux:link>
        </div>

        <!-- Filters -->
        <div class="flex gap-4">
            <div class="flex-1">
                <flux:input 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search payments..." 
                    type="search"
                />
            </div>
            @if($this->tenants->count() > 0)
                <div class="w-48">
                    <flux:select wire:model.live="tenantFilter" placeholder="Filter by tenant">
                        <option value="">All Tenants</option>
                        @foreach($this->tenants as $tenant)
                            <option value="{{ $tenant->id }}">{{ $tenant->user->name }}</option>
                        @endforeach
                    </flux:select>
                </div>
            @endif
            <div class="w-48">
                <flux:select wire:model.live="statusFilter" placeholder="Filter by status">
                    <option value="">All Status</option>
                    <option value="completed">Completed</option>
                    <option value="pending">Pending</option>
                    <option value="failed">Failed</option>
                </flux:select>
            </div>
        </div>

        <!-- Payments Table -->
        <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-neutral-200 dark:border-neutral-700">
                            <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Date</th>
                            @if(auth()->user()->isOwner() || auth()->user()->isManager())
                                <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Tenant</th>
                            @endif
                            <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Amount</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Method</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Reference</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Receipt #</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Status</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Invoice</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->payments as $payment)
                            <tr class="border-b border-neutral-100 dark:border-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-900/50">
                                <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                                    {{ $payment->payment_date->format('M d, Y') }}
                                </td>
                                @if(auth()->user()->isOwner() || auth()->user()->isManager())
                                    <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                                        {{ $payment->tenant->user->name ?? 'N/A' }}
                                    </td>
                                @endif
                                <td class="px-4 py-3 text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                    ₦{{ number_format($payment->amount, 2) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                                    {{ $payment->payment_method ? ucfirst(str_replace('_', ' ', $payment->payment_method)) : 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                                    {{ $payment->transaction_reference ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                                    {{ $payment->receipt_number ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-1 text-xs font-medium
                                        {{ $payment->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                        {{ $payment->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                        {{ $payment->status === 'failed' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                                    ">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                                    @if($payment->invoice)
                                        <flux:link href="{{ route('invoices.show', $payment->invoice) }}" wire:navigate>
                                            {{ $payment->invoice->invoice_number }}
                                        </flux:link>
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->isOwner() || auth()->user()->isManager() ? '8' : '7' }}" class="px-4 py-8 text-center text-neutral-600 dark:text-neutral-400">
                                    No payments found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="border-t border-neutral-200 px-4 py-3 dark:border-neutral-700">
                {{ $this->payments->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>

