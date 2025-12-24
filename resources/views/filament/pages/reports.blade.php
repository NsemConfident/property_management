<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filters Section -->
        <x-filament::section>
            <x-slot name="heading">
                Filters
            </x-slot>
            <x-slot name="description">
                Adjust date range and property to filter reports
            </x-slot>
            
            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                    <input type="date" wire:model.live="startDate" class="fi-input block w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:bg-gray-50 disabled:text-gray-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-primary-500 dark:disabled:bg-transparent dark:disabled:text-gray-400" />
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                    <input type="date" wire:model.live="endDate" class="fi-input block w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:bg-gray-50 disabled:text-gray-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-primary-500 dark:disabled:bg-transparent dark:disabled:text-gray-400" />
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Property (Optional)</label>
                    <select wire:model.live="propertyId" class="fi-input block w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:bg-gray-50 disabled:text-gray-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-primary-500 dark:disabled:bg-transparent dark:disabled:text-gray-400">
                        <option value="">All Properties</option>
                        @foreach($this->getProperties() as $property)
                            <option value="{{ $property->id }}">{{ $property->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </x-filament::section>

        <!-- Report Tabs -->
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                    <button
                        wire:click="$set('selectedReport', 'revenue')"
                        class="border-b-2 py-4 px-1 text-sm font-medium transition-colors {{ $selectedReport === 'revenue' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
                    >
                        Revenue Report
                    </button>
                    <button
                        wire:click="$set('selectedReport', 'occupancy')"
                        class="border-b-2 py-4 px-1 text-sm font-medium transition-colors {{ $selectedReport === 'occupancy' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
                    >
                        Occupancy Rates
                    </button>
                    <button
                        wire:click="$set('selectedReport', 'payments')"
                        class="border-b-2 py-4 px-1 text-sm font-medium transition-colors {{ $selectedReport === 'payments' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
                    >
                        Payment History
                    </button>
                    <button
                        wire:click="$set('selectedReport', 'overdue')"
                        class="border-b-2 py-4 px-1 text-sm font-medium transition-colors {{ $selectedReport === 'overdue' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
                    >
                        Overdue Invoices
                    </button>
                </nav>
            </div>

            <div class="p-6">
                @if($selectedReport === 'revenue')
                    @php
                        $revenueData = $this->getRevenueData();
                        $revenueByProperty = $this->getRevenueByProperty();
                    @endphp

                    <!-- Revenue Stats -->
                    <div class="grid gap-4 md:grid-cols-4 mb-6">
                        <x-filament::section>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</p>
                                <p class="text-2xl font-bold text-success-600 dark:text-success-400">
                                    ₦{{ number_format($revenueData['total_revenue'], 2) }}
                                </p>
                            </div>
                        </x-filament::section>
                        
                        <x-filament::section>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Payments</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ number_format($revenueData['total_payments']) }}
                                </p>
                            </div>
                        </x-filament::section>
                        
                        <x-filament::section>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Average Payment</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    ₦{{ number_format($revenueData['average_payment'], 2) }}
                                </p>
                            </div>
                        </x-filament::section>
                        
                        <x-filament::section>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Period</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                                </p>
                            </div>
                        </x-filament::section>
                    </div>

                    <!-- Monthly Breakdown -->
                    <x-filament::section class="mb-6">
                        <x-slot name="heading">
                            Monthly Revenue Breakdown
                        </x-slot>
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($revenueData['monthly_breakdown'] as $month => $amount)
                                <div class="flex items-center justify-between py-3">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($month)->format('F Y') }}</span>
                                    <span class="font-semibold text-gray-900 dark:text-gray-100">₦{{ number_format($amount, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </x-filament::section>

                    <!-- Revenue by Property -->
                    <x-filament::section>
                        <x-slot name="heading">
                            Revenue by Property
                        </x-slot>
                        <div class="overflow-x-auto">
                            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Property</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Revenue</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Payments</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                                    @foreach($revenueByProperty as $property)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $property['name'] }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-semibold text-success-600 dark:text-success-400">₦{{ number_format($property['total_revenue'], 2) }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-600 dark:text-gray-400">{{ number_format($property['payment_count']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-filament::section>

                @elseif($selectedReport === 'occupancy')
                    @php
                        $occupancyData = $this->getOccupancyData();
                    @endphp

                    <!-- Occupancy Stats -->
                    <div class="grid gap-4 md:grid-cols-4 mb-6">
                        <x-filament::section>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Units</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $occupancyData['total_units'] }}</p>
                            </div>
                        </x-filament::section>
                        
                        <x-filament::section>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Occupied</p>
                                <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $occupancyData['occupied_units'] }}</p>
                            </div>
                        </x-filament::section>
                        
                        <x-filament::section>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Available</p>
                                <p class="text-2xl font-bold text-success-600 dark:text-success-400">{{ $occupancyData['available_units'] }}</p>
                            </div>
                        </x-filament::section>
                        
                        <x-filament::section>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Occupancy Rate</p>
                                <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ number_format($occupancyData['occupancy_rate'], 1) }}%</p>
                            </div>
                        </x-filament::section>
                    </div>

                    <!-- Occupancy by Property -->
                    <x-filament::section>
                        <x-slot name="heading">
                            Occupancy by Property
                        </x-slot>
                        <div class="overflow-x-auto">
                            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Property</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Units</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Occupied</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Occupancy Rate</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                                    @foreach($occupancyData['by_property'] as $property)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $property->name }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-600 dark:text-gray-400">{{ $property->total_units }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-600 dark:text-gray-400">{{ $property->occupied_units }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-semibold text-primary-600 dark:text-primary-400">{{ number_format($property->occupancy_rate, 1) }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-filament::section>

                @elseif($selectedReport === 'payments')
                    @php
                        $paymentData = $this->getPaymentHistory();
                    @endphp

                    <!-- Payment Stats -->
                    <div class="grid gap-4 md:grid-cols-3 mb-6">
                        <x-filament::section>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Amount</p>
                                <p class="text-2xl font-bold text-success-600 dark:text-success-400">
                                    ₦{{ number_format($paymentData['total_amount'], 2) }}
                                </p>
                            </div>
                        </x-filament::section>
                        
                        <x-filament::section>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Payments</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ $paymentData['payments']->count() }}
                                </p>
                            </div>
                        </x-filament::section>
                        
                        <x-filament::section>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Period</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                                </p>
                            </div>
                        </x-filament::section>
                    </div>

                    <!-- Payments by Method -->
                    <x-filament::section class="mb-6">
                        <x-slot name="heading">
                            Payments by Method
                        </x-slot>
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($paymentData['by_method'] as $method => $data)
                                <div class="flex items-center justify-between py-3">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $method)) }}</span>
                                    <div class="flex items-center gap-4">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $data['count'] }} payments</span>
                                        <span class="font-semibold text-gray-900 dark:text-gray-100">₦{{ number_format($data['total'], 2) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-filament::section>

                    <!-- Recent Payments -->
                    <x-filament::section>
                        <x-slot name="heading">
                            Recent Payments
                        </x-slot>
                        <div class="overflow-x-auto">
                            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Tenant</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Property</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Amount</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Method</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                                    @foreach($paymentData['payments']->take(20) as $payment)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $payment->payment_date->format('M d, Y') }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $payment->tenant->user->name ?? 'N/A' }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $payment->tenant->unit->property->name ?? 'N/A' }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-semibold text-success-600 dark:text-success-400">₦{{ number_format($payment->amount, 2) }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'N/A')) }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm">
                                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $payment->status === 'completed' ? 'bg-success-50 text-success-700 ring-success-600/20 dark:bg-success-400/10 dark:text-success-400 dark:ring-success-400/20' : 'bg-warning-50 text-warning-700 ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-400 dark:ring-warning-400/20' }}">
                                                    {{ ucfirst($payment->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-filament::section>

                @elseif($selectedReport === 'overdue')
                    @php
                        $overdueData = $this->getOverdueData();
                    @endphp

                    <!-- Overdue Stats -->
                    <div class="grid gap-4 md:grid-cols-3 mb-6">
                        <x-filament::section>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Overdue</p>
                                <p class="text-2xl font-bold text-danger-600 dark:text-danger-400">
                                    ₦{{ number_format($overdueData['total_overdue'], 2) }}
                                </p>
                            </div>
                        </x-filament::section>
                        
                        <x-filament::section>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Overdue Invoices</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ $overdueData['count'] }}
                                </p>
                            </div>
                        </x-filament::section>
                        
                        <x-filament::section>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Average Overdue</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    ₦{{ $overdueData['count'] > 0 ? number_format($overdueData['total_overdue'] / $overdueData['count'], 2) : '0.00' }}
                                </p>
                            </div>
                        </x-filament::section>
                    </div>

                    <!-- Overdue by Property -->
                    @if($overdueData['by_property']->count() > 0)
                        <x-filament::section class="mb-6">
                            <x-slot name="heading">
                                Overdue by Property
                            </x-slot>
                            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($overdueData['by_property'] as $propertyName => $data)
                                    <div class="flex items-center justify-between py-3">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $propertyName }}</span>
                                        <div class="flex items-center gap-4">
                                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $data['count'] }} invoices</span>
                                            <span class="font-semibold text-danger-600 dark:text-danger-400">₦{{ number_format($data['total'], 2) }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </x-filament::section>
                    @endif

                    <!-- Overdue Invoices List -->
                    <x-filament::section>
                        <x-slot name="heading">
                            Overdue Invoices
                        </x-slot>
                        @if($overdueData['invoices']->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-800">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Invoice #</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Tenant</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Property</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Amount</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Balance</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Due Date</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Days Overdue</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                                        @foreach($overdueData['invoices'] as $invoice)
                                            @php
                                                $daysOverdue = now()->diffInDays($invoice->due_date);
                                            @endphp
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $invoice->invoice_number }}</td>
                                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $invoice->tenant->user->name ?? 'N/A' }}</td>
                                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $invoice->tenant->unit->property->name ?? 'N/A' }}</td>
                                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-600 dark:text-gray-400">₦{{ number_format($invoice->amount, 2) }}</td>
                                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-semibold text-danger-600 dark:text-danger-400">₦{{ number_format($invoice->balance, 2) }}</td>
                                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $invoice->due_date->format('M d, Y') }}</td>
                                                <td class="whitespace-nowrap px-4 py-3 text-sm">
                                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset bg-danger-50 text-danger-700 ring-danger-600/20 dark:bg-danger-400/10 dark:text-danger-400 dark:ring-danger-400/20">
                                                        {{ $daysOverdue }} days
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="py-8 text-center text-gray-500 dark:text-gray-400">No overdue invoices found.</p>
                        @endif
                    </x-filament::section>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
