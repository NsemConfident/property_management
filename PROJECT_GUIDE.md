# Property Payment System - Project Guide

## Overview
This is a Property Payment System with Property Manager Module designed to automate rent collection and management for residential properties. The system includes role-based dashboards for tenants, property owners, and property managers.

## Technology Stack
- **Backend**: Laravel 12
- **Frontend**: Livewire Volt, Flux UI, Tailwind CSS
- **Database**: SQLite (can be changed to MySQL/PostgreSQL)
- **Authentication**: Laravel Fortify

## Project Structure

### Database Models Created
1. **User** - Extended with role-based access (tenant, owner, manager)
2. **Property** - Properties owned by property owners
3. **Unit** - Individual units within properties
4. **Tenant** - Tenant records linked to users and units
5. **Invoice** - Rent invoices generated for tenants
6. **Payment** - Payment records (payment gateway integration to be added later)
7. **Reminder** - Automated reminders for payments

### Features Implemented

#### ✅ Completed
- Database migrations for all core entities
- Eloquent models with relationships
- Role-based access control (middleware)
- Role-based dashboards:
  - **Tenant Dashboard**: View invoices, payments, unit info
  - **Owner Dashboard**: View properties, revenue, units
  - **Manager Dashboard**: Manage properties, view tenants, invoices

#### 🚧 To Be Implemented
- Property management CRUD (create, edit, delete properties)
- Unit management CRUD
- Tenant management (assign tenants to units)
- Invoice generation system
- Payment tracking (manual entry for now, gateway later)
- Automated reminder system using Laravel notifications/queues
- Reports and analytics

## Getting Started

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Create Test Users
You'll need to create users with different roles. You can do this via:
- Tinker: `php artisan tinker`
- Or create a seeder (recommended)

### 3. Set User Roles
After creating users, update their role:
```php
$user = User::find(1);
$user->role = 'owner'; // or 'tenant' or 'manager'
$user->save();
```

### 4. Start Development Server
```bash
npm install
npm run dev
php artisan serve
```

## Database Schema

### Users Table
- `role`: enum('tenant', 'owner', 'manager')
- `phone`: string (nullable)
- `address`: text (nullable)

### Properties Table
- `owner_id`: foreign key to users
- `manager_id`: foreign key to users (nullable)
- `name`, `description`, `address`, `city`, `state`, `country`
- `type`: enum('apartment', 'house', 'commercial', 'other')
- `status`: enum('active', 'inactive', 'maintenance')

### Units Table
- `property_id`: foreign key to properties
- `unit_number`: string (unique per property)
- `monthly_rent`, `deposit`: decimal
- `bedrooms`, `bathrooms`: integer
- `status`: enum('available', 'occupied', 'maintenance', 'reserved')

### Tenants Table
- `user_id`: foreign key to users
- `unit_id`: foreign key to units
- `lease_start_date`, `lease_end_date`: date
- `monthly_rent`: decimal
- `lease_status`: enum('active', 'expired', 'terminated')

### Invoices Table
- `tenant_id`: foreign key to tenants
- `invoice_number`: string (unique, auto-generated)
- `invoice_date`, `due_date`: date
- `amount`, `paid_amount`, `balance`: decimal
- `status`: enum('draft', 'sent', 'paid', 'overdue', 'cancelled')
- `line_items`: json (for invoice details)

### Payments Table
- `tenant_id`: foreign key to tenants
- `invoice_id`: foreign key to invoices (nullable)
- `amount`: decimal
- `payment_date`: date
- `payment_method`: enum('bank_transfer', 'card', 'mobile_money', 'cash', 'other')
- `status`: enum('pending', 'completed', 'failed', 'refunded')
- `transaction_reference`: string (unique, nullable)

### Reminders Table
- `tenant_id`: foreign key to tenants
- `invoice_id`: foreign key to invoices (nullable)
- `type`: enum('payment_due', 'payment_overdue', 'lease_expiry', 'custom')
- `subject`, `message`: text
- `reminder_date`: date
- `status`: enum('pending', 'sent', 'cancelled')
- `channel`: enum('email', 'sms', 'both')

## Next Steps

### 1. Create Seeders
Create database seeders to populate test data:
```bash
php artisan make:seeder PropertySeeder
php artisan make:seeder UnitSeeder
php artisan make:seeder TenantSeeder
```

### 2. Build Property Management Pages
- Create property listing page
- Create property form (create/edit)
- Add property deletion

### 3. Build Unit Management Pages
- Create unit listing page
- Create unit form (create/edit)
- Link units to properties

### 4. Build Tenant Management
- Create tenant assignment form
- Link users to units as tenants
- Create lease management

### 5. Implement Invoice Generation
- Create invoice generation service
- Auto-generate monthly invoices
- Invoice PDF generation (optional)

### 6. Implement Payment Tracking
- Create payment entry form
- Link payments to invoices
- Update invoice balances automatically

### 7. Implement Automated Reminders
- Create reminder service
- Schedule reminders using Laravel queues
- Send email/SMS notifications

### 8. Add Payment Gateway Integration (Later)
- Integrate Nigerian payment gateways (Paystack, Flutterwave, etc.)
- Handle payment callbacks
- Update payment status automatically

## Role-Based Access

### Middleware Usage
```php
Route::middleware(['role:owner,manager'])->group(function () {
    // Routes accessible to owners and managers
});
```

### User Model Methods
```php
$user->isTenant();  // Check if user is tenant
$user->isOwner();   // Check if user is owner
$user->isManager(); // Check if user is manager
```

## Dashboard Routes
- `/dashboard` - Auto-redirects based on user role
- `/dashboard/tenant` - Tenant dashboard
- `/dashboard/owner` - Owner dashboard
- `/dashboard/manager` - Manager dashboard

## Notes
- Payment gateway integration is deferred until core features are complete
- The system uses Nigerian Naira (₦) as currency
- All dates and times use Laravel's Carbon library
- The system is designed to be scalable and maintainable

## Support
For questions or issues, refer to the Laravel and Livewire documentation.

