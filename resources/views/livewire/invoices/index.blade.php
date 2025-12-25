<?php

use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithPagination;

use function Livewire\Volt\layout;

layout('components.layouts.app', ['title' => __('Invoices')]);

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

    public function getInvoicesProperty()
    {
        $user = Auth::user();
        
        $query = Invoice::query()->with(['tenant', 'tenant.user', 'tenant.unit', 'tenant.unit.property']);

        if ($user->isTenant() && $user->tenant) {
            $query->where('tenant_id', $user->tenant->id);
        } elseif ($user->isOwner()) {
            $tenantIds = Tenant::whereIn('unit_id', function($q) use ($user) {
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
            $tenantIds = Tenant::whereIn('unit_id', function($q) use ($user) {
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
                $q->where('invoice_number', 'like', '%' . $this->search . '%')
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

        return $query->orderBy('invoice_date', 'desc')
            ->paginate(15);
    }

    public function getTenantsProperty()
    {
        $user = Auth::user();
        
        if ($user->isTenant()) {
            return collect();
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
            
            return Tenant::whereIn('id', $tenantIds)->with('user')->get();
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
            
            return Tenant::whereIn('id', $tenantIds)->with('user')->get();
        }

        return collect();
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Invoices</h1>
                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">Manage rent invoices</p>
            </div>
            @if(auth()->user()->isOwner() || auth()->user()->isManager())
                <flux:link href="{{ route('invoices.create') }}" variant="primary" wire:navigate>
                    <flux:icon.plus class="size-4" />
                    Generate Invoice
                </flux:link>
            @endif
        </div>

        <!-- Filters -->
        <div class="flex gap-4">
            <div class="flex-1">
                <flux:input 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search invoices..." 
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
                    <option value="draft">Draft</option>
                    <option value="sent">Sent</option>
                    <option value="paid">Paid</option>
                    <option value="overdue">Overdue</option>
                </flux:select>
            </div>
        </div>

        <!-- Invoices Table -->
        <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-neutral-200 dark:border-neutral-700">
                            <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Invoice #</th>
                            @if(auth()->user()->isOwner() || auth()->user()->isManager())
                                <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Tenant</th>
                            @endif
                            <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Date</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Due Date</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Amount</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Balance</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-neutral-600 dark:text-neutral-400">Status</th>
                            <th class="px-4 py-3 text-right text-sm font-medium text-neutral-600 dark:text-neutral-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->invoices as $invoice)
                            <tr class="border-b border-neutral-100 dark:border-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-900/50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-neutral-900 dark:text-neutral-100">{{ $invoice->invoice_number }}</div>
                                </td>
                                @if(auth()->user()->isOwner() || auth()->user()->isManager())
                                    <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                                        {{ $invoice->tenant->user->name ?? 'N/A' }}
                                    </td>
                                @endif
                                <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                                    {{ $invoice->invoice_date->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                                    {{ $invoice->due_date->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                    ₦{{ number_format($invoice->amount, 2) }}
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                    ₦{{ number_format($invoice->balance, 2) }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-1 text-xs font-medium
                                        {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                        {{ $invoice->status === 'sent' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                        {{ $invoice->status === 'overdue' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                                        {{ $invoice->status === 'draft' ? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' : '' }}
                                    ">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        @if(auth()->user()->isTenant() && !$invoice->isPaid())
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
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->isOwner() || auth()->user()->isManager() ? '8' : '7' }}" class="px-4 py-8 text-center text-neutral-600 dark:text-neutral-400">
                                    No invoices found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="border-t border-neutral-200 px-4 py-3 dark:border-neutral-700">
                {{ $this->invoices->links() }}
            </div>
        </div>
    </div>
</div>

