<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\FlutterwaveService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $flutterwaveService;
    protected $invoiceService;

    public function __construct(FlutterwaveService $flutterwaveService, InvoiceService $invoiceService)
    {
        $this->flutterwaveService = $flutterwaveService;
        $this->invoiceService = $invoiceService;
    }

    /**
     * Initialize Flutterwave payment for an invoice
     */
    public function initiatePayment(Invoice $invoice)
    {
        Log::info('Payment initiation started', [
            'invoice_id' => $invoice->id,
            'user_id' => auth()->id(),
            'user_role' => auth()->user()->role ?? 'none',
        ]);

        // Check if invoice is already paid
        if ($invoice->isPaid()) {
            Log::warning('Payment attempt on already paid invoice', ['invoice_id' => $invoice->id]);
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'This invoice has already been paid.');
        }

        // Check if user is authorized (tenant can only pay their own invoices)
        if (auth()->user()->role === 'tenant') {
            $tenant = auth()->user()->tenant ?? null;
            if (!$tenant || $invoice->tenant_id !== $tenant->id) {
                Log::warning('Unauthorized payment attempt', [
                    'invoice_id' => $invoice->id,
                    'tenant_id' => $tenant->id ?? null,
                    'invoice_tenant_id' => $invoice->tenant_id,
                ]);
                abort(403, 'Unauthorized to pay this invoice.');
            }
        }

        try {
            Log::info('Calling Flutterwave service', ['invoice_id' => $invoice->id]);
            // Initialize payment
            $result = $this->flutterwaveService->initializePayment($invoice);
            
            Log::info('Flutterwave service response', [
                'invoice_id' => $invoice->id,
                'success' => $result['success'] ?? false,
            ]);

            if ($result['success']) {
                // Store transaction reference in session for verification
                session([
                    'payment_transaction_ref' => $result['transaction_reference'],
                    'payment_invoice_id' => $invoice->id,
                ]);

                // Redirect to Flutterwave payment page
                return redirect($result['payment_url']);
            }

            Log::error('Payment initialization failed', [
                'invoice_id' => $invoice->id,
                'result' => $result,
            ]);

            return redirect()->route('invoices.show', $invoice)
                ->with('error', $result['message'] ?? 'Failed to initialize payment. Please try again.');
        } catch (\Exception $e) {
            Log::error('Payment initialization exception', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'An error occurred while initializing payment: ' . $e->getMessage());
        }
    }

    /**
     * Handle Flutterwave payment callback
     */
    public function handleCallback(Request $request)
    {
        $transactionId = $request->query('transaction_id');
        $status = $request->query('status');

        if (!$transactionId) {
            return redirect()->route('dashboard')
                ->with('error', 'Invalid payment callback.');
        }

        // Verify payment
        $verification = $this->flutterwaveService->verifyPayment($transactionId);

        if (!$verification['success']) {
            Log::error('Payment verification failed', [
                'transaction_id' => $transactionId,
                'response' => $verification,
            ]);

            return redirect()->route('dashboard')
                ->with('error', 'Payment verification failed. Please contact support.');
        }

        $paymentData = $verification['data'];
        $invoiceId = $paymentData['meta']['invoice_id'] ?? session('payment_invoice_id');

        if (!$invoiceId) {
            Log::error('Invoice ID not found in payment callback', [
                'transaction_id' => $transactionId,
                'payment_data' => $paymentData,
            ]);

            return redirect()->route('dashboard')
                ->with('error', 'Invoice not found. Please contact support.');
        }

        $invoice = Invoice::find($invoiceId);

        if (!$invoice) {
            return redirect()->route('dashboard')
                ->with('error', 'Invoice not found.');
        }

        // Check if payment already exists
        $existingPayment = Payment::where('transaction_reference', $transactionId)->first();

        if ($existingPayment) {
            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Payment has already been processed.');
        }

        // Create payment record
        if ($paymentData['status'] === 'successful') {
            $amount = (float) $paymentData['amount'];
            $tenant = $invoice->tenant;

            $payment = Payment::create([
                'tenant_id' => $tenant->id,
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'payment_date' => now(),
                'payment_method' => $this->mapPaymentMethod($paymentData['payment_type'] ?? 'card'),
                'status' => 'completed',
                'transaction_reference' => $transactionId,
                'notes' => "Payment via Flutterwave. Payment Type: " . ($paymentData['payment_type'] ?? 'N/A'),
            ]);

            // Update invoice
            $this->invoiceService->recordPayment($invoice, $amount);

            // Clear session
            session()->forget(['payment_transaction_ref', 'payment_invoice_id']);

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Payment completed successfully!');
        }

        // Payment failed
        $tenant = $invoice->tenant;
        $failedAmount = isset($paymentData['amount']) ? (float) $paymentData['amount'] : $invoice->balance;
        Payment::create([
            'tenant_id' => $tenant->id,
            'invoice_id' => $invoice->id,
            'amount' => $failedAmount,
            'payment_date' => now(),
            'payment_method' => $this->mapPaymentMethod($paymentData['payment_type'] ?? 'card'),
            'status' => 'failed',
            'transaction_reference' => $transactionId,
            'notes' => "Payment failed via Flutterwave. Status: {$status}",
        ]);

        return redirect()->route('invoices.show', $invoice)
            ->with('error', 'Payment was not successful. Please try again.');
    }

    /**
     * Handle Flutterwave webhook
     */
    public function handleWebhook(Request $request)
    {
        $signature = $request->header('verif-hash');

        if (!$signature) {
            Log::warning('Flutterwave webhook received without signature');
            return response()->json(['message' => 'Missing signature'], 400);
        }

        $payload = $request->all();

        // Verify webhook signature
        if (!$this->flutterwaveService->verifyWebhookSignature($signature, $payload)) {
            Log::warning('Flutterwave webhook signature verification failed', [
                'signature' => $signature,
            ]);
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $event = $payload['event'] ?? null;
        $data = $payload['data'] ?? [];

        if ($event === 'charge.completed' && isset($data['id'])) {
            $transactionId = $data['id'];
            $invoiceId = $data['meta']['invoice_id'] ?? null;

            if ($invoiceId) {
                $invoice = Invoice::find($invoiceId);

                if ($invoice && !$invoice->isPaid()) {
                    // Verify payment
                    $verification = $this->flutterwaveService->verifyPayment($transactionId);

                    if ($verification['success'] && $verification['data']['status'] === 'successful') {
                        $amount = (float) $verification['data']['amount'];
                        $tenant = $invoice->tenant;

                        // Check if payment already exists
                        $existingPayment = Payment::where('transaction_reference', $transactionId)->first();

                        if (!$existingPayment) {
                            Payment::create([
                                'tenant_id' => $tenant->id,
                                'invoice_id' => $invoice->id,
                                'amount' => $amount,
                                'payment_date' => now(),
                                'payment_method' => $this->mapPaymentMethod($verification['data']['payment_type'] ?? 'card'),
                                'status' => 'completed',
                                'transaction_reference' => $transactionId,
                                'notes' => "Payment via Flutterwave webhook. Payment Type: " . ($verification['data']['payment_type'] ?? 'N/A'),
                            ]);

                            // Update invoice
                            $this->invoiceService->recordPayment($invoice, $amount);
                        }
                    }
                }
            }
        }

        return response()->json(['message' => 'Webhook processed'], 200);
    }

    /**
     * Map Flutterwave payment type to our payment method enum
     */
    protected function mapPaymentMethod(string $flutterwaveType): string
    {
        return match (strtolower($flutterwaveType)) {
            'card' => 'card',
            'banktransfer', 'bank_transfer' => 'bank_transfer',
            'mobilemoney', 'mobile_money', 'mobilemoneyghana' => 'mobile_money',
            default => 'other',
        };
    }
}
