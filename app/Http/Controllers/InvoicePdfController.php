<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class InvoicePdfController extends Controller
{
    public function download(Invoice $invoice): Response
    {
        $invoice->load(['tenant.user', 'tenant.unit.property']);

        $pdf = Pdf::loadView('invoices.pdf.invoice', compact('invoice'));

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    public function view(Invoice $invoice): Response
    {
        $invoice->load(['tenant.user', 'tenant.unit.property']);

        $pdf = Pdf::loadView('invoices.pdf.invoice', compact('invoice'));

        return $pdf->stream("invoice-{$invoice->invoice_number}.pdf");
    }
}

