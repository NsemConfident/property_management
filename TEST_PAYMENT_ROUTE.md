# How to Test the Payment Route Directly

## Method 1: Using Your Browser (Easiest)

### Step 1: Get an Invoice ID

**Option A: From the Database**
1. Open your database (phpMyAdmin, TablePlus, or command line)
2. Run: `SELECT id, invoice_number, tenant_id, status, balance FROM invoices WHERE status != 'paid' LIMIT 5;`
3. Copy one of the invoice IDs

**Option B: From Your Application**
1. Log in as a tenant
2. Go to the Invoices page (`/invoices`)
3. Click on an unpaid invoice
4. Look at the URL: `http://127.0.0.1:8000/invoices/1` (the number at the end is the invoice ID)

**Option C: Using Tinker**
```bash
php artisan tinker
```
Then run:
```php
\App\Models\Invoice::where('status', '!=', 'paid')->take(5)->get(['id', 'invoice_number', 'balance']);
```

### Step 2: Test the Route

1. **Make sure you're logged in** (the route requires authentication)
2. Open a new browser tab
3. Enter this URL (replace `1` with your actual invoice ID):
   ```
   http://127.0.0.1:8000/invoices/1/pay
   ```
4. Press Enter

### Step 3: What Should Happen

**If it works:**
- You'll be redirected to Flutterwave's payment page
- You'll see Flutterwave's payment interface

**If there's an error:**
- You'll see an error page with details
- Check what the error says

**Common errors:**
- `403 Forbidden` - You're not authorized (make sure you're logged in as the tenant who owns the invoice)
- `404 Not Found` - Invoice doesn't exist (wrong ID)
- `500 Internal Server Error` - Check Laravel logs for details

## Method 2: Using cURL (Command Line)

Open PowerShell or Command Prompt and run:

```bash
# First, get your session cookie (you need to be logged in)
# Then run:
curl -v "http://127.0.0.1:8000/invoices/1/pay" -H "Cookie: laravel_session=YOUR_SESSION_COOKIE"
```

Replace:
- `1` with your invoice ID
- `YOUR_SESSION_COOKIE` with your actual session cookie (get it from browser DevTools)

## Method 3: Check Route is Working

Test if the route exists:

```bash
php artisan route:list --name=payment.initiate
```

You should see:
```
GET|HEAD  invoices/{invoice}/pay  payment.initiate  PaymentController@initiatePayment
```

## Method 4: Check Logs While Testing

1. Open a terminal and run:
   ```bash
   tail -f storage/logs/laravel.log
   ```
   Or on Windows PowerShell:
   ```powershell
   Get-Content storage\logs\laravel.log -Tail 50 -Wait
   ```

2. In another terminal/browser, test the route
3. Watch the logs for any errors or messages

## Quick Test Checklist

- [ ] Laravel server is running (`php artisan serve`)
- [ ] You're logged in as a tenant
- [ ] You have an unpaid invoice
- [ ] The invoice belongs to your tenant account
- [ ] Flutterwave credentials are in `.env`
- [ ] You've restarted the server after adding credentials

## Troubleshooting

**If nothing happens when you click:**
1. Open browser DevTools (F12)
2. Go to Network tab
3. Click the button
4. See if a request is made
5. Check the response

**If you get redirected but see an error:**
- Check Laravel logs: `storage/logs/laravel.log`
- Look for Flutterwave API errors
- Verify your API keys are correct

**If you see "Unauthorized":**
- Make sure you're logged in
- Make sure the invoice belongs to your tenant account
- Check that you're using the correct invoice ID

