<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .invoice-info {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .invoice-info-left, .invoice-info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .invoice-info-right {
            text-align: right;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>INVOICE</h1>
    </div>

    <div class="invoice-info">
        <div class="invoice-info-left">
            <div class="section">
                <div class="section-title">Bill To:</div>
                <div>{{ $invoice->tenant->user->name ?? 'N/A' }}</div>
                <div>{{ $invoice->tenant->user->email ?? 'N/A' }}</div>
                <div>{{ $invoice->tenant->user->phone ?? '' }}</div>
            </div>
            <div class="section">
                <div class="section-title">Property:</div>
                <div>{{ $invoice->tenant->unit->property->name ?? 'N/A' }}</div>
                <div>{{ $invoice->tenant->unit->property->address ?? '' }}</div>
                <div>Unit: {{ $invoice->tenant->unit->unit_number ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="invoice-info-right">
            <div class="section">
                <div><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</div>
                <div><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('F j, Y') }}</div>
                <div><strong>Due Date:</strong> {{ $invoice->due_date->format('F j, Y') }}</div>
                <div><strong>Status:</strong> {{ strtoupper($invoice->status) }}</div>
            </div>
        </div>
    </div>

    @if($invoice->description)
    <div class="section">
        <div class="section-title">Description:</div>
        <div>{{ $invoice->description }}</div>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @if($invoice->line_items && count($invoice->line_items) > 0)
                @foreach($invoice->line_items as $item)
                <tr>
                    <td>{{ $item['description'] ?? 'N/A' }}</td>
                    <td class="text-right">₦{{ number_format($item['amount'] ?? 0, 2) }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td>Rent Payment</td>
                    <td class="text-right">₦{{ number_format($invoice->amount, 2) }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td><strong>Total Amount</strong></td>
                <td class="text-right"><strong>₦{{ number_format($invoice->amount, 2) }}</strong></td>
            </tr>
            @if($invoice->paid_amount > 0)
            <tr>
                <td>Paid Amount</td>
                <td class="text-right">₦{{ number_format($invoice->paid_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td><strong>Balance Due</strong></td>
                <td class="text-right"><strong>₦{{ number_format($invoice->balance, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    @if($invoice->notes)
    <div class="section">
        <div class="section-title">Notes:</div>
        <div>{{ $invoice->notes }}</div>
    </div>
    @endif

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>This is a computer-generated invoice. No signature required.</p>
    </div>
</body>
</html>

