<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filters -->
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                    <input type="date" wire:model.live="startDate" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                    <input type="date" wire:model.live="endDate" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Property (Optional)</label>
                    <select wire:model.live="propertyId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                        <option value="">All Properties</option>
                        @foreach($this->getProperties() as $property)
                            <option value="{{ $property->id }}">{{ $property->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Report Tabs -->
        <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="flex space-x-8 px-6" aria-label="Tabs">
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
                        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Revenue</p>
                            <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">
                                ₦{{ number_format($revenueData['total_revenue'], 2) }}
                            </p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Payments</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ number_format($revenueData['total_payments']) }}
                            </p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Average Payment</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">
                                ₦{{ number_format($revenueData['average_payment'], 2) }}
                            </p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Period</p>
                            <p class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                            </p>
                        </div>
                    </div>

                    <!-- Monthly Breakdown -->
                    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Monthly Revenue Breakdown</h3>
                        <div class="space-y-2">
                            @foreach($revenueData['monthly_breakdown'] as $month => $amount)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($month)->format('F Y') }}</span>
                                    <span class="font-semibold text-gray-900 dark:text-gray-100">₦{{ number_format($amount, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Revenue by Property -->
                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Revenue by Property</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-gray-700">
                                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-400">Property</th>
                                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-600 dark:text-gray-400">Revenue</th>
                                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-600 dark:text-gray-400">Payments</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($revenueByProperty as $property)
                                        <tr class="border-b border-gray-100 dark:border-gray-800">
                                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $property['name'] }}</td>
                                            <td class="px-4 py-2 text-right text-sm font-semibold text-green-600 dark:text-green-400">₦{{ number_format($property['total_revenue'], 2) }}</td>
                                            <td class="px-4 py-2 text-right text-sm text-gray-600 dark:text-gray-400">{{ number_format($property['payment_count']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                @elseif($selectedReport === 'occupancy')
                    @php
                        $occupancyData = $this->getOccupancyData();
                    @endphp

                    <!-- Occupancy Stats -->
                    <div class="grid gap-4 md:grid-cols-4 mb-6">
                        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Units</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $occupancyData['total_units'] }}</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Occupied</p>
                            <p class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $occupancyData['occupied_units'] }}</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Available</p>
                            <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">{{ $occupancyData['available_units'] }}</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Occupancy Rate</p>
                            <p class="mt-2 text-2xl font-bold text-primary-600 dark:text-primary-400">{{ number_format($occupancyData['occupancy_rate'], 1) }}%</p>
                        </div>
                    </div>

                    <!-- Occupancy by Property -->
                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Occupancy by Property</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-gray-700">
                                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-400">Property</th>
                                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-600 dark:text-gray-400">Total Units</th>
                                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-600 dark:text-gray-400">Occupied</th>
                                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-600 dark:text-gray-400">Occupancy Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($occupancyData['by_property'] as $property)
                                        <tr class="border-b border-gray-100 dark:border-gray-800">
                                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $property->name }}</td>
                                            <td class="px-4 py-2 text-right text-sm text-gray-600 dark:text-gray-400">{{ $property->total_units }}</td>
                                            <td class="px-4 py-2 text-right text-sm text-gray-600 dark:text-gray-400">{{ $property->occupied_units }}</td>
                                            <td class="px-4 py-2 text-right text-sm font-semibold text-primary-600 dark:text-primary-400">{{ number_format($property->occupancy_rate, 1) }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                @elseif($selectedReport === 'payments')
                    @php
                        $paymentData = $this->getPaymentHistory();
                    @endphp

                    <!-- Payment Stats -->
                    <div class="grid gap-4 md:grid-cols-3 mb-6">
                        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Amount</p>
                            <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">
                                ₦{{ number_format($paymentData['total_amount'], 2) }}
                            </p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Payments</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ $paymentData['payments']->count() }}
                            </p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Period</p>
                            <p class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                            </p>
                        </div>
                    </div>

                    <!-- Payments by Method -->
                    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Payments by Method</h3>
                        <div class="space-y-2">
                            @foreach($paymentData['by_method'] as $method => $data)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $method)) }}</span>
                                    <div class="flex items-center gap-4">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $data['count'] }} payments</span>
                                        <span class="font-semibold text-gray-900 dark:text-gray-100">₦{{ number_format($data['total'], 2) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Recent Payments -->
                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Payments</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-gray-700">
                                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-400">Date</th>
                                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-400">Tenant</th>
                                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-400">Property</th>
                                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-600 dark:text-gray-400">Amount</th>
                                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-400">Method</th>
                                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-400">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($paymentData['payments']->take(20) as $payment)
                                        <tr class="border-b border-gray-100 dark:border-gray-800">
                                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ $payment->payment_date->format('M d, Y') }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $payment->tenant->user->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ $payment->tenant->unit->property->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-2 text-right text-sm font-semibold text-green-600 dark:text-green-400">₦{{ number_format($payment->amount, 2) }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'N/A')) }}</td>
                                            <td class="px-4 py-2 text-sm">
                                                <span class="rounded-full px-2 py-1 text-xs {{ $payment->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                                    {{ ucfirst($payment->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                @elseif($selectedReport === 'overdue')
                    @php
                        $overdueData = $this->getOverdueData();
                    @endphp

                    <!-- Overdue Stats -->
                    <div class="grid gap-4 md:grid-cols-3 mb-6">
                        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Overdue</p>
                            <p class="mt-2 text-2xl font-bold text-red-600 dark:text-red-400">
                                ₦{{ number_format($overdueData['total_overdue'], 2) }}
                            </p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Overdue Invoices</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ $overdueData['count'] }}
                            </p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Average Overdue</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">
                                ₦{{ $overdueData['count'] > 0 ? number_format($overdueData['total_overdue'] / $overdueData['count'], 2) : '0.00' }}
                            </p>
                        </div>
                    </div>

                    <!-- Overdue by Property -->
                    @if($overdueData['by_property']->count() > 0)
                        <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Overdue by Property</h3>
                            <div class="space-y-2">
                                @foreach($overdueData['by_property'] as $propertyName => $data)
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $propertyName }}</span>
                                        <div class="flex items-center gap-4">
                                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $data['count'] }} invoices</span>
                                            <span class="font-semibold text-red-600 dark:text-red-400">₦{{ number_format($data['total'], 2) }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Overdue Invoices List -->
                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Overdue Invoices</h3>
                        @if($overdueData['invoices']->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b border-gray-200 dark:border-gray-700">
                                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-400">Invoice #</th>
                                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-400">Tenant</th>
                                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-400">Property</th>
                                            <th class="px-4 py-2 text-right text-sm font-medium text-gray-600 dark:text-gray-400">Amount</th>
                                            <th class="px-4 py-2 text-right text-sm font-medium text-gray-600 dark:text-gray-400">Balance</th>
                                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-400">Due Date</th>
                                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-400">Days Overdue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($overdueData['invoices'] as $invoice)
                                            @php
                                                $daysOverdue = now()->diffInDays($invoice->due_date);
                                            @endphp
                                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $invoice->invoice_number }}</td>
                                                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ $invoice->tenant->user->name ?? 'N/A' }}</td>
                                                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ $invoice->tenant->unit->property->name ?? 'N/A' }}</td>
                                                <td class="px-4 py-2 text-right text-sm text-gray-600 dark:text-gray-400">₦{{ number_format($invoice->amount, 2) }}</td>
                                                <td class="px-4 py-2 text-right text-sm font-semibold text-red-600 dark:text-red-400">₦{{ number_format($invoice->balance, 2) }}</td>
                                                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ $invoice->due_date->format('M d, Y') }}</td>
                                                <td class="px-4 py-2 text-sm">
                                                    <span class="rounded-full px-2 py-1 text-xs bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                        {{ $daysOverdue }} days
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-center py-8 text-gray-600 dark:text-gray-400">No overdue invoices found.</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>

