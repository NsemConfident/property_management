# Flutterwave Payment Gateway Setup Guide

This guide will help you set up Flutterwave payment gateway integration in Sandbox/Test Mode.

## Prerequisites

1. A Flutterwave account (sign up at https://flutterwave.com)
2. Access to your Flutterwave dashboard

## Step 1: Get Your Flutterwave Test Credentials

1. Log in to your Flutterwave dashboard: https://dashboard.flutterwave.com
2. Navigate to **Settings** > **API Keys**
3. You'll see your **Test API Keys** section
4. Copy the following:
   - **Public Key** (starts with `FLWPUBK_TEST_...`)
   - **Secret Key** (starts with `FLWSECK_TEST_...`)
   - **Secret Hash** (for webhook verification)

## Step 2: Configure Environment Variables

Add the following to your `.env` file:

```env
# Flutterwave Configuration (Test Mode)
FLW_PUBLIC_KEY=FLWPUBK_TEST_your_public_key_here
FLW_SECRET_KEY=FLWSECK_TEST_your_secret_key_here
FLW_SECRET_HASH=your_secret_hash_here
FLW_ENVIRONMENT=test
FLW_CURRENCY=NGN
```

## Step 3: Configure Webhook URL

1. In your Flutterwave dashboard, go to **Settings** > **Webhooks**
2. **Enable webhook preferences:**
   - ✅ Enable "Receive webhook response in JSON format"
   - ✅ Enable "Enable v3 webhooks" (important!)
   - ✅ Enable "Enable webhook retries" (optional but recommended)
   - ✅ Enable "Enable resend webhook from the dashboard" (optional)
3. **Add your webhook URL:**
   - Look for a section to add webhook URLs (might be labeled "Webhook URLs" or "Add Webhook")
   - Enter your webhook URL:
     ```
     https://yourdomain.com/payment/webhook
     ```
   - For local development with ngrok:
     ```
     https://your-ngrok-url.ngrok-free.app/payment/webhook
     ```
   - **Note:** Flutterwave v3 webhooks automatically send events for all payment transactions, including `charge.completed`. You don't need to manually select events - they're sent automatically when payments are completed.
4. **Save** the webhook URL

**Alternative:** If you can't find where to add the webhook URL:
- Look for a "Webhook URL" field in the webhook settings
- Or check if there's a separate "Webhooks" tab/section
- The webhook URL might be set at the account level rather than per-event

## Step 4: Test the Integration

### Test Card Details (Sandbox Mode)

Use these test cards for testing:

**Successful Payment:**
- Card Number: `5531886652142950`
- CVV: `564`
- Expiry: Any future date (e.g., `12/25`)
- PIN: `3310`
- OTP: `123456`

**Failed Payment:**
- Card Number: `4084084084084081`
- CVV: `408`
- Expiry: Any future date
- PIN: `0000`

### Testing Steps

1. Log in as a tenant
2. Navigate to an unpaid invoice
3. Click "Pay Now with Flutterwave"
4. You'll be redirected to Flutterwave's payment page
5. Use the test card details above
6. Complete the payment
7. You'll be redirected back to the invoice page with a success message

## Step 5: Switch to Production

When ready for production:

1. Get your **Live API Keys** from Flutterwave dashboard
2. Update your `.env` file:
   ```env
   FLW_PUBLIC_KEY=FLWPUBK_live_your_public_key_here
   FLW_SECRET_KEY=FLWSECK_live_your_secret_key_here
   FLW_SECRET_HASH=your_live_secret_hash_here
   FLW_ENVIRONMENT=live
   ```
3. Update the webhook URL in Flutterwave dashboard to your production URL
4. Test thoroughly before going live

## Payment Flow

1. **Payment Initiation**: Tenant clicks "Pay Now" on an invoice
2. **Redirect**: User is redirected to Flutterwave payment page
3. **Payment**: User completes payment on Flutterwave
4. **Callback**: Flutterwave redirects back to our callback URL
5. **Verification**: We verify the payment with Flutterwave API
6. **Recording**: Payment is recorded in the database
7. **Invoice Update**: Invoice balance and status are updated

## Webhook Flow

1. Flutterwave sends a webhook when payment is completed
2. We verify the webhook signature
3. We verify the payment transaction
4. We record the payment and update the invoice

## Troubleshooting

### Payment Not Initializing
- Check that your API keys are correct
- Ensure `FLW_ENVIRONMENT` is set to `test` for sandbox
- Check Laravel logs for errors

### Payment Verification Failing
- Verify the transaction ID is correct
- Check that the payment was actually successful on Flutterwave
- Review Laravel logs for detailed error messages

### Webhook Not Working
- Ensure webhook URL is accessible (use ngrok for local testing)
- Verify `FLW_SECRET_HASH` matches your Flutterwave dashboard
- Check that webhook events are enabled in Flutterwave dashboard

## Support

For Flutterwave API issues, refer to:
- Flutterwave Documentation: https://developer.flutterwave.com/docs
- Flutterwave Support: support@flutterwave.com

