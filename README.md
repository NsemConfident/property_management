# Property Payment Management System

A comprehensive property management system with automated rent collection, invoice generation, payment tracking, and Flutterwave payment gateway integration. Built with Laravel 12, Livewire Volt, and Filament Admin Panel.

## 🎯 Project Overview

This system automates rent collection and management for residential properties. It provides role-based dashboards for tenants, property owners, property managers, and administrators to track payments, generate invoices, receive automated reminders, and process payments through Flutterwave.

## ✨ Key Features

### Core Features
- **Property Management**: Complete CRUD operations for properties
- **Unit Management**: Manage rental units within properties
- **Tenant Management**: Assign tenants to units, track lease information
- **Invoice Generation**: Automated and manual invoice creation
- **Payment Tracking**: Record and track payments (manual and online)
- **Flutterwave Integration**: Secure online payment processing
- **Automated Reminders**: Customizable email reminders for payments and lease expiry
- **Reports & Analytics**: Comprehensive reporting with charts and data exports
- **Role-Based Access**: Separate dashboards for Tenants, Owners, Managers, and Admins

### Advanced Features
- **Custom Reminder Templates**: Admin-customizable reminder messages
- **PDF Invoice Export**: Generate and download invoice PDFs
- **Excel Reports**: Export reports, payments, invoices, and tenant data
- **Filament Admin Panel**: Full-featured admin interface with analytics
- **Payment Gateway**: Flutterwave integration for online payments (Test/Live mode)

## 🛠️ Technology Stack

- **Backend**: Laravel 12
- **Frontend**: Livewire Volt, Flux UI, Tailwind CSS
- **Admin Panel**: Filament 4.x
- **Database**: SQLite (configurable for MySQL/PostgreSQL)
- **Authentication**: Laravel Fortify
- **Payment Gateway**: Flutterwave API
- **PDF Generation**: DomPDF
- **Excel Export**: Maatwebsite Excel

## 📋 Requirements

- PHP 8.2 or higher
- Composer
- Node.js and npm
- SQLite (or MySQL/PostgreSQL)
- Flutterwave account (for payment processing)

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd property_management
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Configuration

Copy the `.env.example` file to `.env`:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

### 4. Configure Database

Edit `.env` and set your database configuration:

```env
DB_CONNECTION=sqlite
# Or for MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=property_management
# DB_USERNAME=root
# DB_PASSWORD=
```

For SQLite, create the database file:

```bash
touch database/database.sqlite
```

### 5. Run Migrations

```bash
php artisan migrate
```

### 6. Seed Database (Optional)

Populate with test data:

```bash
php artisan db:seed
```

This creates:
- Test users (tenant, owner, manager, admin)
- Sample properties and units
- Test invoices and payments
- Default reminder templates

### 7. Configure Flutterwave

Add your Flutterwave credentials to `.env`:

```env
FLW_PUBLIC_KEY=FLWPUBK_TEST_your_public_key
FLW_SECRET_KEY=FLWSECK_TEST_your_secret_key
FLW_SECRET_HASH=your_secret_hash
FLW_ENVIRONMENT=test
FLW_CURRENCY=NGN
```

See [FLUTTERWAVE_SETUP.md](FLUTTERWAVE_SETUP.md) for detailed setup instructions.

### 8. Build Assets

```bash
npm run build
```

Or for development:

```bash
npm run dev
```

### 9. Start Development Server

```bash
php artisan serve
```

Visit `http://127.0.0.1:8000` in your browser.

## 👥 User Roles

### Tenant
- View own invoices and payments
- Pay invoices online via Flutterwave
- View payment history
- Access tenant dashboard

### Property Owner
- Manage properties and units
- Generate invoices
- View reports and analytics
- Track payments

### Property Manager
- Manage assigned properties
- Generate invoices
- View reports
- Track tenant payments

### Admin
- Full system access via Filament admin panel
- Manage all users, properties, units, tenants
- View comprehensive analytics and reports
- Customize reminder templates
- Export data (PDF/Excel)

## 📖 Usage Guide

### For Tenants

1. **Login**: Use your tenant credentials
2. **View Invoices**: Go to "Invoices" from the sidebar
3. **Pay Invoice**: Click "Pay Now" on any unpaid invoice
4. **Complete Payment**: You'll be redirected to Flutterwave to complete payment
5. **View Payments**: Check "Recent Payments" on your dashboard

### For Property Owners/Managers

1. **Login**: Use your owner/manager credentials
2. **Manage Properties**: Go to "Properties" to add/edit properties
3. **Manage Units**: Add units to properties
4. **Assign Tenants**: Create tenant records and assign to units
5. **Generate Invoices**: Create invoices manually or use automated generation
6. **View Reports**: Access reports and analytics from the sidebar

### For Administrators

1. **Access Admin Panel**: Visit `/admin` or click "Admin Panel" in sidebar
2. **Manage Resources**: Use Filament resources to manage all entities
3. **View Analytics**: Check dashboard widgets for key metrics
4. **Customize Reminders**: Edit reminder templates in "Reminder Templates"
5. **Export Data**: Use export actions in resources (Excel) or download PDFs

## 🔧 Configuration

### Flutterwave Payment Gateway

See [FLUTTERWAVE_SETUP.md](FLUTTERWAVE_SETUP.md) for complete setup guide.

### Automated Reminders

Reminders are sent automatically via scheduled tasks. Configure in `routes/console.php`:

```php
Schedule::command('reminders:send')->daily();
```

### Email Configuration

Configure mail settings in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

## 📁 Project Structure

```
property_management/
├── app/
│   ├── Console/Commands/      # Scheduled commands
│   ├── Exports/               # Excel export classes
│   ├── Filament/              # Admin panel resources
│   ├── Http/Controllers/      # Payment controllers
│   ├── Models/                # Eloquent models
│   ├── Notifications/         # Email notifications
│   └── Services/              # Business logic services
├── config/                    # Configuration files
├── database/
│   ├── migrations/           # Database migrations
│   └── seeders/             # Database seeders
├── resources/
│   └── views/
│       ├── livewire/         # Livewire Volt components
│       └── invoices/pdf/    # PDF templates
└── routes/
    ├── web.php              # Web routes
    └── console.php          # Scheduled tasks
```

## 🔐 Default Test Credentials

After seeding, you can login with:

- **Admin**: `admin@example.com` / `password`
- **Owner**: `owner@example.com` / `password`
- **Manager**: `manager@example.com` / `password`
- **Tenant**: `tenant@example.com` / `password`

## 🧪 Testing

### Test Flutterwave Payment

1. Use test card: `5531886652142950`
2. CVV: `564`
3. Expiry: Any future date
4. PIN: `3310`
5. OTP: `123456`

See [FLUTTERWAVE_SETUP.md](FLUTTERWAVE_SETUP.md) for more test cards.

## 🚢 Deployment

### Production Checklist

1. **Environment**: Set `APP_ENV=production` in `.env`
2. **SSL Certificates**: Remove `withoutVerifying()` from `FlutterwaveService.php` or configure proper SSL certificates
3. **Flutterwave**: Switch to live API keys
4. **Webhook URL**: Update Flutterwave webhook to production URL
5. **Database**: Use MySQL or PostgreSQL in production
6. **Optimize**: Run `php artisan optimize`
7. **Queue**: Set up queue workers for reminders
8. **Scheduler**: Configure cron job for scheduled tasks

See [DEPLOYMENT.md](DEPLOYMENT.md) for detailed deployment guide.

## 📚 Additional Documentation

- [FLUTTERWAVE_SETUP.md](FLUTTERWAVE_SETUP.md) - Flutterwave payment gateway setup
- [NGROK_SETUP.md](NGROK_SETUP.md) - ngrok setup for webhook testing
- [PAYMENT_TROUBLESHOOTING.md](PAYMENT_TROUBLESHOOTING.md) - Payment troubleshooting
- [SSL_CERTIFICATE_FIX.md](SSL_CERTIFICATE_FIX.md) - SSL certificate configuration

## 🐛 Troubleshooting

### Common Issues

**Payment not redirecting:**
- Check Flutterwave credentials in `.env`
- Verify SSL certificate configuration
- Check Laravel logs: `storage/logs/laravel.log`

**Webhook not working:**
- Ensure ngrok is running (for local testing)
- Verify webhook URL in Flutterwave dashboard
- Check `FLW_SECRET_HASH` matches dashboard

**Reminders not sending:**
- Verify queue workers are running
- Check scheduled tasks: `php artisan schedule:list`
- Review email configuration

See [PAYMENT_TROUBLESHOOTING.md](PAYMENT_TROUBLESHOOTING.md) for more solutions.

## 📝 License

This project is for educational purposes (Nigerian student project).

## 👨‍💻 Development

### Running Tests

```bash
php artisan test
```

### Code Style

```bash
./vendor/bin/pint
```

## 🤝 Contributing

This is a student project. For improvements or bug fixes, please create an issue or pull request.

## 📞 Support

For issues or questions:
1. Check the troubleshooting guides
2. Review Laravel logs
3. Check Flutterwave documentation: https://developer.flutterwave.com/docs

---

**Built with ❤️ using Laravel, Livewire, and Filament**

