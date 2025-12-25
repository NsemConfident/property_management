# Deployment Guide

This guide covers deploying the Property Payment Management System to production.

## Pre-Deployment Checklist

- [ ] All features tested and working
- [ ] Database migrations run successfully
- [ ] Environment variables configured
- [ ] SSL certificates configured
- [ ] Flutterwave live API keys obtained
- [ ] Webhook URL configured in Flutterwave
- [ ] Email service configured
- [ ] Queue workers configured
- [ ] Scheduled tasks configured (cron)

## Step 1: Server Requirements

- PHP 8.2 or higher
- Composer
- Node.js and npm (for asset compilation)
- MySQL or PostgreSQL (recommended for production)
- Web server (Nginx or Apache)
- SSL certificate (required for Flutterwave)

## Step 2: Environment Configuration

### Update `.env` for Production

```env
APP_NAME="Property Management System"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database (use MySQL or PostgreSQL)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=property_management
DB_USERNAME=your_username
DB_PASSWORD=your_secure_password

# Flutterwave Live Keys
FLW_PUBLIC_KEY=FLWPUBK_live_your_live_public_key
FLW_SECRET_KEY=FLWSECK_live_your_live_secret_key
FLW_SECRET_HASH=your_live_secret_hash
FLW_ENVIRONMENT=live
FLW_CURRENCY=NGN

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Queue Configuration
QUEUE_CONNECTION=database
# Or use Redis: QUEUE_CONNECTION=redis
```

## Step 3: Fix SSL Certificate Issue

### Remove `withoutVerifying()` for Production

Edit `app/Services/FlutterwaveService.php`:

**Option 1: Environment-based (Recommended)**

```php
$http = Http::withHeaders([
    'Authorization' => 'Bearer ' . $this->secretKey,
    'Content-Type' => 'application/json',
]);

// Only disable verification in local/testing
if (app()->environment('local')) {
    $http = $http->withoutVerifying();
}

$response = $http->post("{$this->baseUrl}/payments", $paymentData);
```

**Option 2: Configure Proper SSL Certificates**

1. Download CA certificate bundle: https://curl.se/ca/cacert.pem
2. Update `php.ini`:
   ```ini
   curl.cainfo = "/path/to/cacert.pem"
   openssl.cafile = "/path/to/cacert.pem"
   ```
3. Restart PHP-FPM/web server

## Step 4: Database Setup

### Run Migrations

```bash
php artisan migrate --force
```

### Seed Production Data (Optional)

```bash
php artisan db:seed --class=ReminderTemplateSeeder
```

**Note:** Don't run the full seeder in production (it creates test data).

## Step 5: Build Production Assets

```bash
npm run build
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Step 6: Configure Web Server

### Nginx Configuration Example

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com;

    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;

    root /path/to/property_management/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Apache Configuration Example

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    Redirect permanent / https://yourdomain.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot /path/to/property_management/public

    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key

    <Directory /path/to/property_management/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

## Step 7: Configure Queue Workers

### Using Supervisor (Recommended)

Create `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/property_management/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/property_management/storage/logs/worker.log
stopwaitsecs=3600
```

Start supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

### Using Systemd

Create `/etc/systemd/system/laravel-worker.service`:

```ini
[Unit]
Description=Laravel Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /path/to/property_management/artisan queue:work --sleep=3 --tries=3

[Install]
WantedBy=multi-user.target
```

Enable and start:

```bash
sudo systemctl enable laravel-worker
sudo systemctl start laravel-worker
```

## Step 8: Configure Scheduled Tasks

Add to crontab (`crontab -e`):

```bash
* * * * * cd /path/to/property_management && php artisan schedule:run >> /dev/null 2>&1
```

Or using systemd timer (recommended):

Create `/etc/systemd/system/laravel-scheduler.service`:

```ini
[Unit]
Description=Laravel Scheduler
After=network.target

[Service]
User=www-data
Type=oneshot
ExecStart=/usr/bin/php /path/to/property_management/artisan schedule:run
```

Create `/etc/systemd/system/laravel-scheduler.timer`:

```ini
[Unit]
Description=Run Laravel Scheduler Every Minute
Requires=laravel-scheduler.service

[Timer]
OnCalendar=*:0/1
Persistent=true

[Install]
WantedBy=timers.target
```

Enable:

```bash
sudo systemctl enable laravel-scheduler.timer
sudo systemctl start laravel-scheduler.timer
```

## Step 9: Configure Flutterwave Webhook

1. Log in to Flutterwave Dashboard
2. Go to **Settings** > **Webhooks**
3. Add webhook URL: `https://yourdomain.com/payment/webhook`
4. Select event: **charge.completed**
5. Save webhook

## Step 10: File Permissions

```bash
cd /path/to/property_management
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## Step 11: Security Hardening

1. **Hide `.env` file**: Ensure web server doesn't serve `.env`
2. **Disable debug mode**: `APP_DEBUG=false`
3. **Use strong passwords**: For database and all accounts
4. **Enable HTTPS**: Required for Flutterwave
5. **Regular updates**: Keep Laravel and dependencies updated
6. **Backup database**: Set up regular backups

## Step 12: Monitoring

### Log Monitoring

Monitor Laravel logs:

```bash
tail -f storage/logs/laravel.log
```

### Queue Monitoring

Check queue status:

```bash
php artisan queue:monitor
```

### Scheduled Tasks

List scheduled tasks:

```bash
php artisan schedule:list
```

## Post-Deployment Testing

1. ✅ Test user login
2. ✅ Test invoice creation
3. ✅ Test payment flow (use Flutterwave test mode first)
4. ✅ Test webhook receives notifications
5. ✅ Test email reminders
6. ✅ Test PDF generation
7. ✅ Test Excel exports
8. ✅ Test admin panel access

## Backup Strategy

### Database Backup

```bash
# MySQL
mysqldump -u username -p property_management > backup.sql

# PostgreSQL
pg_dump property_management > backup.sql
```

### File Backup

```bash
tar -czf backup-$(date +%Y%m%d).tar.gz storage/ public/uploads/
```

### Automated Backups

Set up cron job for daily backups:

```bash
0 2 * * * /path/to/backup-script.sh
```

## Rollback Procedure

If deployment fails:

1. Restore database from backup
2. Restore files from backup
3. Revert code changes
4. Clear caches: `php artisan cache:clear`
5. Restart services

## Support

For deployment issues:
1. Check Laravel logs
2. Check web server logs
3. Verify file permissions
4. Check queue worker status
5. Verify scheduled tasks are running

---

**Remember**: Always test in a staging environment before deploying to production!

