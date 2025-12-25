# Quick Test Steps

1. **Get an invoice ID:**
   - Go to your invoices page
   - Or check the database: `SELECT id FROM invoices LIMIT 1;`

2. **Test the route directly in browser:**
   ```
   http://127.0.0.1:8000/invoices/1/pay
   ```
   (Replace `1` with your actual invoice ID)

3. **Check what happens:**
   - Does it redirect?
   - Do you see an error page?
   - Does it show a blank page?

4. **Check Laravel logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```
   Or on Windows PowerShell:
   ```powershell
   Get-Content storage\logs\laravel.log -Tail 50 -Wait
   ```

5. **Check browser console:**
   - Press F12
   - Go to Console tab
   - Click the button
   - Look for any errors

