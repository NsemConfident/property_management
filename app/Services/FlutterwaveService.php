<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlutterwaveService
{
    protected $publicKey;
    protected $secretKey;
    protected $baseUrl;
    protected $currency;

    public function __construct()
    {
        $this->publicKey = config('flutterwave.public_key');
        $this->secretKey = config('flutterwave.secret_key');
        $this->baseUrl = config('flutterwave.base_url', 'https://api.flutterwave.com/v3');
        $this->currency = config('flutterwave.currency', 'NGN');

        // Validate credentials
        if (empty($this->secretKey)) {
            Log::warning('Flutterwave secret key is not configured');
        }
        if (empty($this->publicKey)) {
            Log::warning('Flutterwave public key is not configured');
        }
    }

    /**
     * Initialize a payment transaction
     *
     * @param Invoice $invoice
     * @param array $customerData
     * @return array
     */
    public function initializePayment(Invoice $invoice, array $customerData = []): array
    {
        try {
            $tenant = $invoice->tenant;
            $user = $tenant->user;
            $property = $tenant->unit->property;

            // Generate unique transaction reference
            $txRef = 'INV-' . $invoice->id . '-' . time();

            // Prepare payment data
            $paymentData = [
                'tx_ref' => $txRef,
                'amount' => (string) $invoice->balance,
                'currency' => $this->currency,
                'payment_options' => 'card,banktransfer,ussd,mobilemoneyghana',
                'redirect_url' => route('payment.callback'),
                'customer' => [
                    'email' => $customerData['email'] ?? $user->email,
                    'name' => $customerData['name'] ?? $user->name,
                    'phone_number' => $customerData['phone'] ?? $user->phone ?? '08000000000',
                ],
                'customizations' => [
                    'title' => 'Property Rent Payment',
                    'description' => "Payment for Invoice #{$invoice->invoice_number} - {$property->name}",
                ],
                'meta' => [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'tenant_id' => $tenant->id,
                ],
            ];

            // Validate credentials before making request
            if (empty($this->secretKey)) {
                return [
                    'success' => false,
                    'message' => 'Flutterwave secret key is not configured. Please add FLW_SECRET_KEY to your .env file.',
                ];
            }

            // Make API request
            // Note: withoutVerifying() is only for local development/testing
            // Remove this in production and configure proper SSL certificates
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->secretKey,
                    'Content-Type' => 'application/json',
                ])->post("{$this->baseUrl}/payments", $paymentData);

            $responseData = $response->json();

            Log::info('Flutterwave payment initialization response', [
                'status_code' => $response->status(),
                'response' => $responseData,
                'invoice_id' => $invoice->id,
            ]);

            if ($response->successful() && isset($responseData['status']) && $responseData['status'] === 'success') {
                return [
                    'success' => true,
                    'payment_url' => $responseData['data']['link'],
                    'transaction_reference' => $txRef,
                ];
            }

            Log::error('Flutterwave payment initialization failed', [
                'status_code' => $response->status(),
                'response' => $responseData,
                'invoice_id' => $invoice->id,
                'payment_data' => $paymentData,
            ]);

            $errorMessage = $responseData['message'] ?? 'Failed to initialize payment';
            if (isset($responseData['data']['message'])) {
                $errorMessage = $responseData['data']['message'];
            }

            return [
                'success' => false,
                'message' => $errorMessage,
            ];
        } catch (\Exception $e) {
            Log::error('Flutterwave payment initialization error: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'message' => 'An error occurred while initializing payment. Please try again.',
            ];
        }
    }

    /**
     * Verify a payment transaction
     *
     * @param string $transactionId
     * @return array
     */
    public function verifyPayment(string $transactionId): array
    {
        try {
            // Note: withoutVerifying() is only for local development/testing
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->secretKey,
                    'Content-Type' => 'application/json',
                ])->get("{$this->baseUrl}/transactions/{$transactionId}/verify");

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['status']) && $responseData['status'] === 'success') {
                $transactionData = $responseData['data'];
                
                if ($transactionData['status'] === 'successful') {
                    return [
                        'success' => true,
                        'data' => $transactionData,
                    ];
                }
            }

            Log::error('Flutterwave payment verification failed', [
                'transaction_id' => $transactionId,
                'response' => $responseData,
            ]);

            return [
                'success' => false,
                'message' => $responseData['message'] ?? 'Payment verification failed',
                'data' => $responseData['data'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Flutterwave payment verification error: ' . $e->getMessage(), [
                'transaction_id' => $transactionId,
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'message' => 'An error occurred while verifying payment.',
            ];
        }
    }

    /**
     * Verify webhook signature
     *
     * @param string $signature
     * @param array $payload
     * @return bool
     */
    public function verifyWebhookSignature(string $signature, array $payload): bool
    {
        $secretHash = config('flutterwave.secret_hash');
        
        if (empty($secretHash)) {
            Log::warning('Flutterwave secret hash not configured');
            return false;
        }

        // Flutterwave sends the hash in the header, we need to verify it
        $computedHash = hash_hmac('sha256', json_encode($payload), $secretHash);
        
        return hash_equals($computedHash, $signature);
    }
}
