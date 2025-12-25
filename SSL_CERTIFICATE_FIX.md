# SSL Certificate Fix for Flutterwave

## Quick Fix (For Testing Only)

I've added `withoutVerifying()` to the HTTP requests. This disables SSL certificate verification, which is **ONLY safe for local development/testing**.

**⚠️ IMPORTANT:** Remove `withoutVerifying()` before deploying to production!

## Proper Fix (For Production)

### Option 1: Download CA Certificate Bundle (Recommended)

1. Download the CA certificate bundle:
   - Go to: https://curl.se/ca/cacert.pem
   - Save it to: `C:\php\extras\ssl\cacert.pem` (or any location)

2. Update `php.ini`:
   ```ini
   curl.cainfo = "C:\php\extras\ssl\cacert.pem"
   openssl.cafile = "C:\php\extras\ssl\cacert.pem"
   ```

3. Restart your web server/Laravel server

### Option 2: Use XAMPP's Built-in Certificate

If you're using XAMPP, the certificate is usually at:
```ini
curl.cainfo = "C:\xampp\php\extras\ssl\cacert.pem"
```

### Option 3: Environment-Based Solution

Update `app/Services/FlutterwaveService.php`:

```php
$http = Http::withHeaders([...]);

// Only disable verification in local/testing
if (app()->environment('local')) {
    $http = $http->withoutVerifying();
}

$response = $http->post(...);
```

## Verify the Fix

After applying the fix, test the payment route again:
```
http://127.0.0.1:8000/invoices/5/pay
```

You should now be redirected to Flutterwave's payment page!

