# Payment Troubleshooting Guide

If clicking "Pay Now" doesn't redirect to Flutterwave, follow these steps:

## Step 1: Check Browser Console

1. Open your browser's Developer Tools (F12)
2. Go to the **Console** tab
3. Click the "Pay Now" button
4. Look for any JavaScript errors (red messages)
5. Check the **Network** tab to see if the request is being made

## Step 2: Check Laravel Logs

Check your Laravel logs for errors:

```bash
tail -f storage/logs/laravel.log
```

Or on Windows:
```powershell
Get-Content storage\logs\laravel.log -Tail 50 -Wait
```

Look for:
- Flutterwave API errors
- Missing configuration errors
- Payment initialization errors

## Step 3: Verify Flutterwave Configuration

Make sure your `.env` file has:

```env
FLW_PUBLIC_KEY=FLWPUBK_TEST_your_key_here
FLW_SECRET_KEY=FLWSECK_TEST_your_key_here
FLW_SECRET_HASH=your_secret_hash_here
FLW_ENVIRONMENT=test
FLW_CURRENCY=NGN
```

**Important:** 
- Keys must start with `FLWPUBK_TEST_` and `FLWSECK_TEST_` for test mode
- No spaces or quotes around the values
- Restart your Laravel server after changing `.env`

## Step 4: Test the Route Directly

Try accessing the payment route directly in your browser:

```
http://127.0.0.1:8000/invoices/{invoice_id}/pay
```

Replace `{invoice_id}` with an actual invoice ID from your database.

If you see an error page, that will tell you what's wrong.

## Step 5: Check Invoice Status

Make sure:
- The invoice is not already paid
- You're logged in as a tenant
- The invoice belongs to your tenant account

## Step 6: Common Issues

### Issue: "Flutterwave secret key is not configured"
**Solution:** Add `FLW_SECRET_KEY` to your `.env` file and restart the server.

### Issue: "Unauthorized to pay this invoice"
**Solution:** Make sure you're logged in as the tenant who owns the invoice.

### Issue: Button does nothing (no redirect)
**Possible causes:**
1. JavaScript error preventing redirect
2. Livewire intercepting the click (fixed by adding `wire:navigate="false"`)
3. Flutterwave API returning an error
4. Missing or invalid API keys

### Issue: "Failed to initialize payment"
**Check:**
1. API keys are correct
2. Internet connection
3. Flutterwave service is not down
4. Laravel logs for specific error message

## Step 7: Manual Test

You can test the Flutterwave service directly:

```php
// In tinker: php artisan tinker
$invoice = \App\Models\Invoice::first();
$service = app(\App\Services\FlutterwaveService::class);
$result = $service->initializePayment($invoice);
dd($result);
```

This will show you the exact response from Flutterwave.

## Still Not Working?

1. Check that your Laravel server is running: `php artisan serve`
2. Clear all caches: `php artisan config:clear && php artisan cache:clear`
3. Verify the route exists: `php artisan route:list --name=payment`
4. Check browser network tab to see if the request is being made
5. Share the error message from Laravel logs

