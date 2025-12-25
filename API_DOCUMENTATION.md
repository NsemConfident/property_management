# API Documentation

Technical documentation for the Property Payment Management System.

## Table of Contents

1. [Architecture](#architecture)
2. [Models](#models)
3. [Services](#services)
4. [Controllers](#controllers)
5. [Routes](#routes)
6. [Database Schema](#database-schema)

## Architecture

### Technology Stack

- **Framework**: Laravel 12
- **Frontend**: Livewire Volt, Flux UI
- **Admin Panel**: Filament 4.x
- **Database**: SQLite (configurable)
- **Payment Gateway**: Flutterwave API v3

### Directory Structure

```
app/
├── Console/Commands/          # Artisan commands
│   └── SendRemindersCommand.php
├── Exports/                   # Excel export classes
│   ├── InvoicesExport.php
│   ├── PaymentsExport.php
│   ├── RevenueReportExport.php
│   └── TenantsExport.php
├── Filament/                  # Admin panel
│   ├── Pages/
│   ├── Resources/
│   └── Widgets/
├── Http/Controllers/
│   ├── InvoicePdfController.php
│   └── PaymentController.php
├── Models/                    # Eloquent models
│   ├── Invoice.php
│   ├── Payment.php
│   ├── Property.php
│   ├── Reminder.php
│   ├── ReminderTemplate.php
│   ├── Tenant.php
│   ├── Unit.php
│   └── User.php
├── Notifications/
│   └── PaymentReminderNotification.php
└── Services/                  # Business logic
    ├── FlutterwaveService.php
    ├── InvoiceService.php
    ├── ReminderService.php
    ├── ReportService.php
    └── TemplateService.php
```

## Models

### User Model

**Location**: `app/Models/User.php`

**Relationships**:
- `ownedProperties()` - HasMany Property
- `managedProperties()` - HasMany Property
- `tenant()` - HasOne Tenant

**Methods**:
- `isTenant()` - Check if user is tenant
- `isOwner()` - Check if user is owner
- `isManager()` - Check if user is manager
- `isAdmin()` - Check if user is admin

### Property Model

**Location**: `app/Models/Property.php`

**Relationships**:
- `owner()` - BelongsTo User
- `manager()` - BelongsTo User
- `units()` - HasMany Unit

### Unit Model

**Location**: `app/Models/Unit.php`

**Relationships**:
- `property()` - BelongsTo Property
- `tenant()` - HasOne Tenant

### Tenant Model

**Location**: `app/Models/Tenant.php`

**Relationships**:
- `user()` - BelongsTo User
- `unit()` - BelongsTo Unit
- `invoices()` - HasMany Invoice
- `payments()` - HasMany Payment
- `reminders()` - HasMany Reminder

### Invoice Model

**Location**: `app/Models/Invoice.php`

**Relationships**:
- `tenant()` - BelongsTo Tenant
- `payments()` - HasMany Payment
- `reminders()` - HasMany Reminder

**Methods**:
- `isPaid()` - Check if invoice is fully paid
- `isOverdue()` - Check if invoice is overdue
- `generateInvoiceNumber()` - Generate unique invoice number

### Payment Model

**Location**: `app/Models/Payment.php`

**Relationships**:
- `tenant()` - BelongsTo Tenant
- `invoice()` - BelongsTo Invoice

**Methods**:
- `isCompleted()` - Check if payment is completed

### Reminder Model

**Location**: `app/Models/Reminder.php`

**Relationships**:
- `invoice()` - BelongsTo Invoice
- `tenant()` - BelongsTo Tenant

### ReminderTemplate Model

**Location**: `app/Models/ReminderTemplate.php`

**Methods**:
- `getActiveForType()` - Get active template for type
- `getAvailableVariables()` - Get available template variables

## Services

### FlutterwaveService

**Location**: `app/Services/FlutterwaveService.php`

**Methods**:

#### `initializePayment(Invoice $invoice, array $customerData = [])`

Initialize a Flutterwave payment transaction.

**Parameters**:
- `Invoice $invoice` - Invoice to pay
- `array $customerData` - Optional customer data override

**Returns**: `array`
```php
[
    'success' => true,
    'payment_url' => 'https://...',
    'transaction_reference' => 'INV-1-1234567890'
]
```

#### `verifyPayment(string $transactionId)`

Verify a payment transaction with Flutterwave.

**Parameters**:
- `string $transactionId` - Flutterwave transaction ID

**Returns**: `array`
```php
[
    'success' => true,
    'data' => [...]
]
```

#### `verifyWebhookSignature(string $signature, array $payload)`

Verify Flutterwave webhook signature.

**Parameters**:
- `string $signature` - Webhook signature from header
- `array $payload` - Webhook payload

**Returns**: `bool`

### InvoiceService

**Location**: `app/Services/InvoiceService.php`

**Methods**:

#### `generateMonthlyInvoice(Tenant $tenant, Carbon $month = null)`

Generate monthly invoice for a tenant.

**Parameters**:
- `Tenant $tenant` - Tenant to generate invoice for
- `Carbon $month` - Month to generate for (default: current month)

**Returns**: `Invoice`

#### `recordPayment(Invoice $invoice, float $amount, array $paymentData = [])`

Record payment against invoice and update balance.

**Parameters**:
- `Invoice $invoice` - Invoice to record payment for
- `float $amount` - Payment amount
- `array $paymentData` - Additional payment data

**Returns**: `void`

### ReminderService

**Location**: `app/Services/ReminderService.php`

**Methods**:

#### `createPaymentDueReminder(Invoice $invoice, int $daysBeforeDue = 3)`

Create payment due reminder.

**Parameters**:
- `Invoice $invoice` - Invoice to create reminder for
- `int $daysBeforeDue` - Days before due date

**Returns**: `Reminder|null`

#### `sendReminder(Reminder $reminder)`

Send a reminder notification.

**Parameters**:
- `Reminder $reminder` - Reminder to send

**Returns**: `void`

### ReportService

**Location**: `app/Services/ReportService.php`

**Methods**:

#### `getRevenueData(array $filters = [])`

Get revenue report data.

**Parameters**:
- `array $filters` - Date range, property filters

**Returns**: `array`

#### `getOccupancyData(array $filters = [])`

Get occupancy rate data.

**Parameters**:
- `array $filters` - Property filters

**Returns**: `array`

### TemplateService

**Location**: `app/Services/TemplateService.php`

**Methods**:

#### `replaceVariables(string $templateString, array $data)`

Replace template variables in string.

**Parameters**:
- `string $templateString` - Template with variables
- `array $data` - Data to replace with

**Returns**: `string`

## Controllers

### PaymentController

**Location**: `app/Http/Controllers/PaymentController.php`

#### `initiatePayment(Invoice $invoice)`

Initialize Flutterwave payment.

**Route**: `GET /invoices/{invoice}/pay`

**Middleware**: `auth`

**Returns**: Redirect to Flutterwave payment page

#### `handleCallback(Request $request)`

Handle Flutterwave payment callback.

**Route**: `GET /payment/callback`

**Parameters**:
- `transaction_id` - Flutterwave transaction ID
- `status` - Payment status

**Returns**: Redirect to invoice page

#### `handleWebhook(Request $request)`

Handle Flutterwave webhook.

**Route**: `POST /payment/webhook`

**Headers**:
- `verif-hash` - Webhook signature

**Returns**: JSON response

### InvoicePdfController

**Location**: `app/Http/Controllers/InvoicePdfController.php`

#### `download(Invoice $invoice)`

Download invoice as PDF.

**Route**: `GET /invoices/{invoice}/pdf`

**Returns**: PDF download

#### `view(Invoice $invoice)`

View invoice PDF in browser.

**Route**: `GET /invoices/{invoice}/view-pdf`

**Returns**: PDF view

## Routes

### Web Routes

**File**: `routes/web.php`

#### Authentication Routes
- `GET /login` - Login page
- `POST /login` - Login action
- `POST /logout` - Logout
- `GET /register` - Registration page
- `POST /register` - Registration action

#### Dashboard Routes
- `GET /dashboard` - Main dashboard
- `GET /dashboard/tenant` - Tenant dashboard
- `GET /dashboard/owner` - Owner dashboard
- `GET /dashboard/manager` - Manager dashboard

#### Property Routes
- `GET /properties` - List properties
- `GET /properties/create` - Create property form
- `GET /properties/{property}` - View property
- `GET /properties/{property}/edit` - Edit property form

#### Invoice Routes
- `GET /invoices` - List invoices
- `GET /invoices/create` - Create invoice form
- `GET /invoices/{invoice}` - View invoice
- `GET /invoices/{invoice}/pdf` - Download PDF
- `GET /invoices/{invoice}/view-pdf` - View PDF

#### Payment Routes
- `GET /invoices/{invoice}/pay` - Initiate payment
- `GET /payment/callback` - Payment callback
- `POST /payment/webhook` - Payment webhook

#### Admin Routes
- `GET /admin` - Filament admin panel
- All Filament resources under `/admin/*`

### Console Routes

**File**: `routes/console.php`

#### Scheduled Tasks
- `reminders:send` - Send pending reminders (daily)

## Database Schema

### users
- `id` - Primary key
- `name` - User name
- `email` - Email address (unique)
- `password` - Hashed password
- `role` - Enum: tenant, owner, manager, admin
- `phone` - Phone number
- `address` - Address
- `timestamps`

### properties
- `id` - Primary key
- `name` - Property name
- `address` - Property address
- `description` - Description
- `property_type` - Type of property
- `owner_id` - Foreign key to users
- `manager_id` - Foreign key to users
- `timestamps`

### units
- `id` - Primary key
- `property_id` - Foreign key to properties
- `unit_number` - Unit number
- `unit_type` - Type of unit
- `monthly_rent` - Monthly rent amount
- `status` - Enum: available, occupied, maintenance
- `timestamps`

### tenants
- `id` - Primary key
- `user_id` - Foreign key to users
- `unit_id` - Foreign key to units
- `lease_start_date` - Lease start date
- `lease_end_date` - Lease end date
- `monthly_rent` - Monthly rent
- `lease_status` - Enum: active, expired, terminated
- `emergency_contact_name` - Emergency contact
- `emergency_contact_phone` - Emergency phone
- `notes` - Additional notes
- `timestamps`

### invoices
- `id` - Primary key
- `tenant_id` - Foreign key to tenants
- `invoice_number` - Unique invoice number
- `invoice_date` - Invoice date
- `due_date` - Due date
- `amount` - Total amount
- `paid_amount` - Amount paid
- `balance` - Outstanding balance
- `status` - Enum: draft, sent, paid, overdue, cancelled
- `description` - Invoice description
- `line_items` - JSON: Line items array
- `notes` - Additional notes
- `paid_at` - Date paid (nullable)
- `timestamps`

### payments
- `id` - Primary key
- `tenant_id` - Foreign key to tenants
- `invoice_id` - Foreign key to invoices (nullable)
- `amount` - Payment amount
- `payment_date` - Payment date
- `payment_method` - Enum: bank_transfer, card, mobile_money, cash, other
- `status` - Enum: pending, completed, failed, refunded
- `transaction_reference` - Transaction reference (unique, nullable)
- `notes` - Payment notes
- `receipt_number` - Receipt number (unique, nullable)
- `timestamps`

### reminders
- `id` - Primary key
- `invoice_id` - Foreign key to invoices (nullable)
- `tenant_id` - Foreign key to tenants
- `type` - Enum: payment_due, payment_overdue, lease_expiry, custom
- `subject` - Reminder subject
- `message` - Reminder message
- `scheduled_for` - Scheduled send date
- `sent_at` - Date sent (nullable)
- `status` - Enum: pending, sent, failed
- `timestamps`

### reminder_templates
- `id` - Primary key
- `name` - Template name
- `type` - Enum: payment_due, payment_overdue, lease_expiry, custom
- `subject` - Email subject
- `message` - Email message
- `is_active` - Boolean: Active status
- `days_before` - Integer: Days before (nullable)
- `timestamps`

## Environment Variables

### Application
```env
APP_NAME="Property Management System"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
```

### Database
```env
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite
```

### Flutterwave
```env
FLW_PUBLIC_KEY=FLWPUBK_TEST_...
FLW_SECRET_KEY=FLWSECK_TEST_...
FLW_SECRET_HASH=...
FLW_ENVIRONMENT=test
FLW_CURRENCY=NGN
```

### Mail
```env
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
```

## Commands

### Artisan Commands

#### Send Reminders
```bash
php artisan reminders:send
```

Sends all pending reminders.

#### Schedule List
```bash
php artisan schedule:list
```

Lists all scheduled tasks.

## Events & Notifications

### Notifications

#### PaymentReminderNotification

**Location**: `app/Notifications/PaymentReminderNotification.php`

Sends email reminders for payments.

**Channels**: `mail`

## Exports

### Excel Exports

All export classes located in `app/Exports/`:

- `InvoicesExport` - Export invoices to Excel
- `PaymentsExport` - Export payments to Excel
- `RevenueReportExport` - Export revenue report
- `TenantsExport` - Export tenants to Excel

## Security

### Middleware

- `auth` - Authentication required
- `role:tenant,owner,manager` - Role-based access
- `EnsureUserIsAdmin` - Admin-only access

### Authorization

- Tenants can only access their own invoices/payments
- Owners can only access their properties
- Managers can only access assigned properties
- Admins have full access

---

**Last Updated**: December 2024

