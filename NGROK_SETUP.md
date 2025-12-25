# ngrok Setup Guide for Flutterwave Webhooks

This guide will help you set up ngrok to test Flutterwave webhooks locally.

## Step 1: Start Your Laravel Application

First, make sure your Laravel application is running:

```bash
php artisan serve
```

Your app should be running on `http://127.0.0.1:8000` or `http://localhost:8000`

## Step 2: Start ngrok

Open a new terminal/command prompt and run:

```bash
ngrok http 8000
```

**Note:** If your Laravel app is running on a different port, replace `8000` with your port number.

## Step 3: Get Your ngrok URL

After starting ngrok, you'll see output like this:

```
Session Status                online
Account                       Your Name (Plan: Free)
Version                       3.x.x
Region                        United States (us)
Latency                       -
Web Interface                 http://127.0.0.1:4040
Forwarding                    https://abc123xyz.ngrok-free.app -> http://localhost:8000
```

**Copy the HTTPS URL** (the one that starts with `https://`). This is your public URL.

Example: `https://abc123xyz.ngrok-free.app`

## Step 4: Configure Flutterwave Webhook

1. Log in to your Flutterwave dashboard: https://dashboard.flutterwave.com
2. Navigate to **Settings** > **Webhooks**
3. Click **Add Webhook** or **Create Webhook**
4. Enter your webhook URL:
   ```
   https://your-ngrok-url.ngrok-free.app/payment/webhook
   ```
   Replace `your-ngrok-url` with your actual ngrok URL from Step 3.
   
   Example: `https://abc123xyz.ngrok-free.app/payment/webhook`
5. Select the event: **charge.completed**
6. Click **Save** or **Create Webhook**

## Step 5: Test the Payment Flow

### Option A: Test with Browser (Recommended)

1. **Start ngrok** (keep it running in a terminal)
2. **Start Laravel** (in another terminal: `php artisan serve`)
3. **Log in** to your application as a tenant
4. **Navigate** to an unpaid invoice
5. **Click** "Pay Now with Flutterwave"
6. You'll be redirected to Flutterwave's payment page
7. **Use test card details:**
   - Card Number: `5531886652142950`
   - CVV: `564`
   - Expiry: Any future date (e.g., `12/25`)
   - PIN: `3310`
   - OTP: `123456`
8. **Complete** the payment
9. You'll be redirected back to your invoice page
10. **Check** that the payment was recorded and invoice updated

### Option B: Monitor Webhook Requests

1. Open ngrok's web interface: http://127.0.0.1:4040
2. Go to the **Inspect** tab
3. Make a payment (follow steps 3-9 from Option A)
4. You'll see the webhook request in the ngrok interface
5. Check the request/response details

## Step 6: Verify Payment in Database

After completing a payment, verify it was recorded:

1. Check the `payments` table in your database
2. Check that the invoice balance was updated
3. Check Laravel logs: `storage/logs/laravel.log`

## Troubleshooting

### ngrok URL Not Working

- Make sure ngrok is still running
- Verify your Laravel app is running on port 8000
- Check that the URL in Flutterwave dashboard matches your ngrok URL exactly

### Webhook Not Received

1. **Check ngrok web interface** (http://127.0.0.1:4040) to see if request was received
2. **Check Laravel logs** for errors
3. **Verify webhook URL** in Flutterwave dashboard matches your ngrok URL
4. **Check webhook signature** - ensure `FLW_SECRET_HASH` in `.env` matches Flutterwave dashboard

### Payment Not Recording

1. Check Laravel logs for errors
2. Verify database connection
3. Check that the webhook handler is working (check logs)
4. Verify the payment was actually successful on Flutterwave dashboard

### ngrok Free Plan Limitations

- Free ngrok URLs change each time you restart ngrok
- You'll need to update the webhook URL in Flutterwave dashboard each time
- Consider using ngrok's reserved domains (paid feature) for consistent URLs

## Quick Test Checklist

- [ ] Laravel app running (`php artisan serve`)
- [ ] ngrok running (`ngrok http 8000`)
- [ ] Webhook URL configured in Flutterwave dashboard
- [ ] `.env` file has Flutterwave credentials
- [ ] Logged in as tenant
- [ ] Unpaid invoice available
- [ ] Test card details ready

## Next Steps After Testing

Once you've confirmed everything works:

1. **For Production:** Use your actual domain instead of ngrok
2. **Update webhook URL** in Flutterwave dashboard to production URL
3. **Switch to live API keys** in `.env` file
4. **Test thoroughly** before going live

