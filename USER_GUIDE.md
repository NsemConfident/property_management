# User Guide

Complete guide for using the Property Payment Management System.

## Table of Contents

1. [Getting Started](#getting-started)
2. [Tenant Guide](#tenant-guide)
3. [Property Owner Guide](#property-owner-guide)
4. [Property Manager Guide](#property-manager-guide)
5. [Admin Guide](#admin-guide)

## Getting Started

### First Login

1. Visit the application URL
2. Click "Login" or "Register"
3. Enter your credentials
4. You'll be redirected to your role-specific dashboard

### Navigation

- **Sidebar**: Main navigation menu (left side)
- **Dashboard**: Role-specific overview page
- **Top Bar**: User profile and logout

---

## Tenant Guide

### Dashboard Overview

Your tenant dashboard shows:
- **Total Paid**: Sum of all completed payments
- **Total Due**: Outstanding invoice balances
- **Overdue Invoices**: Count of overdue invoices
- **Current Unit**: Your rental unit information
- **Upcoming Invoices**: Invoices due soon
- **Recent Payments**: Your payment history

### Viewing Invoices

1. Click **"Invoices"** in the sidebar
2. View all your invoices in a table
3. Filter by status (Draft, Sent, Paid, Overdue)
4. Click the eye icon to view invoice details

### Paying an Invoice

#### Method 1: From Dashboard

1. Go to your dashboard
2. Find the invoice in "Upcoming Invoices" or "Overdue Invoices"
3. Click **"Pay Now"** button
4. You'll be redirected to Flutterwave payment page
5. Complete payment using:
   - Card
   - Bank Transfer
   - Mobile Money
6. After payment, you'll be redirected back

#### Method 2: From Invoice Page

1. Go to **"Invoices"** in sidebar
2. Click on an unpaid invoice
3. Click **"Pay Now with Flutterwave"** button
4. Complete payment on Flutterwave

#### Method 3: From Invoice List

1. Go to **"Invoices"**
2. Click **"Pay Now"** next to any unpaid invoice
3. Complete payment

### Viewing Payment History

1. Go to your **Dashboard**
2. Scroll to **"Recent Payments"** section
3. View payment date, amount, method, and reference

### Downloading Invoice PDF

1. Go to **"Invoices"**
2. Click on an invoice
3. Click **"Download PDF"** button (if available)

---

## Property Owner Guide

### Dashboard Overview

Your owner dashboard shows:
- Properties you own
- Total revenue
- Occupancy rates
- Recent activities

### Managing Properties

#### Create a Property

1. Click **"Properties"** in sidebar
2. Click **"Create Property"** or **"Add Property"**
3. Fill in:
   - Property Name
   - Address
   - Description
   - Property Type
4. Click **"Save"**

#### Edit a Property

1. Go to **"Properties"**
2. Click on a property
3. Click **"Edit"**
4. Make changes
5. Click **"Save"**

### Managing Units

#### Create a Unit

1. Go to **"Properties"**
2. Click on a property
3. Click **"Add Unit"**
4. Fill in:
   - Unit Number
   - Unit Type
   - Monthly Rent
   - Status
5. Click **"Save"**

### Managing Tenants

#### Assign a Tenant

1. Go to **"Tenants"** in sidebar
2. Click **"Create Tenant"** or **"Add Tenant"**
3. Fill in:
   - User Information (name, email, phone)
   - Select Unit
   - Lease Start/End Dates
   - Monthly Rent
   - Emergency Contact
4. Click **"Save"**

**Note**: If the user doesn't exist, create them first or the system will create one.

### Generating Invoices

#### Manual Invoice Creation

1. Go to **"Invoices"** in sidebar
2. Click **"Generate Invoice"**
3. Select Tenant
4. Fill in:
   - Invoice Date
   - Due Date
   - Amount
   - Description
   - Line Items (optional)
5. Click **"Save"**

#### Automated Invoice Generation

Invoices can be generated automatically via:
- Scheduled tasks (monthly)
- Admin panel actions
- API calls

### Viewing Reports

1. Click **"Reports"** in sidebar
2. Select report type:
   - Revenue Report
   - Occupancy Rates
   - Payment History
   - Overdue Invoices
3. Apply filters (date range, property)
4. View data and charts
5. Export to Excel (if available)

### Recording Manual Payments

1. Go to **"Payments"** in sidebar
2. Click **"Create Payment"**
3. Select Tenant and Invoice
4. Fill in:
   - Amount
   - Payment Date
   - Payment Method
   - Reference Number
5. Click **"Save"**

---

## Property Manager Guide

Property Managers have similar access to Owners but only for properties assigned to them.

### Key Differences

- Can only manage assigned properties
- Can only view invoices for assigned properties
- Reports filtered to assigned properties only

### Workflow

1. **View Assigned Properties**: Go to **"Properties"** (only shows your properties)
2. **Manage Units**: Add/edit units in your properties
3. **Assign Tenants**: Create tenant records
4. **Generate Invoices**: Create invoices for tenants
5. **Track Payments**: View payment history
6. **View Reports**: Access analytics for your properties

---

## Admin Guide

### Accessing Admin Panel

1. Click **"Admin Panel"** in sidebar (only visible to admins)
2. Or visit `/admin` directly
3. Login with admin credentials

### Admin Dashboard

The Filament admin dashboard shows:
- **Revenue Statistics**: Total revenue, growth, trends
- **Revenue Charts**: Visual revenue breakdowns
- **Property Performance**: Revenue by property
- **Payment Analytics**: Payment methods, monthly trends
- **Occupancy Stats**: Unit occupancy rates
- **Invoice Status**: Invoice status breakdown
- **Overdue Invoices**: List of overdue invoices
- **Recent Payments**: Latest payment transactions
- **Recent Invoices**: Latest invoices
- **Properties Overview**: Property summary table
- **Active Tenants**: Current tenant list

### Managing Resources

#### Users

1. Go to **"Users"** in admin sidebar
2. View all users
3. Create, edit, or delete users
4. Change user roles

#### Properties

1. Go to **"Properties"**
2. Manage all properties
3. Assign owners and managers
4. View property details

#### Units

1. Go to **"Units"**
2. Manage all units across all properties
3. Update unit status
4. Edit rent amounts

#### Tenants

1. Go to **"Tenants"**
2. View all tenants
3. Assign tenants to units
4. Update lease information
5. Export tenant data to Excel

#### Invoices

1. Go to **"Invoices"**
2. View all invoices
3. Create invoices manually
4. Download PDF invoices
5. Export to Excel

#### Payments

1. Go to **"Payments"**
2. View all payments
3. Record manual payments
4. Export payment data

#### Reminders

1. Go to **"Reminders"**
2. View all sent reminders
3. Check reminder status

#### Reminder Templates

1. Go to **"Reminder Templates"**
2. Create custom reminder templates
3. Edit existing templates
4. Activate/deactivate templates

**Template Types:**
- **Payment Due**: Sent before invoice due date
- **Payment Overdue**: Sent for overdue invoices
- **Lease Expiry**: Sent before lease expiration
- **Custom**: General purpose templates

**Template Variables:**
- `{{tenant_name}}` - Tenant's full name
- `{{invoice_number}}` - Invoice number
- `{{amount_due}}` - Amount due (formatted)
- `{{due_date}}` - Due date
- And more...

### Reports Page

1. Go to **"Reports"** in admin sidebar
2. Select report type:
   - Revenue Report
   - Occupancy Rates
   - Payment History
   - Overdue Invoices
3. Apply filters:
   - Date range
   - Property (optional)
4. View detailed analytics
5. Export to Excel (for revenue report)

### Exporting Data

#### Excel Exports

Available in:
- Invoices resource (header action)
- Payments resource (header action)
- Tenants resource (header action)
- Reports page (revenue report)

#### PDF Exports

- Invoice PDFs: Click "Download PDF" on invoice detail page

---

## Common Tasks

### For Tenants

- **Pay Invoice**: Dashboard → Upcoming Invoices → Pay Now
- **View Payment History**: Dashboard → Recent Payments
- **Download Invoice**: Invoices → Click Invoice → Download PDF

### For Owners/Managers

- **Create Invoice**: Invoices → Generate Invoice
- **Record Payment**: Payments → Create Payment
- **View Reports**: Reports → Select Report Type
- **Add Tenant**: Tenants → Create Tenant

### For Admins

- **Customize Reminders**: Reminder Templates → Edit Template
- **View Analytics**: Admin Dashboard
- **Export Data**: Use export buttons in resources
- **Manage Users**: Users → Create/Edit User

---

## Tips & Best Practices

### For Tenants

- Pay invoices before due date to avoid overdue status
- Keep your contact information updated
- Download invoice PDFs for your records
- Check dashboard regularly for new invoices

### For Owners/Managers

- Generate invoices at the beginning of each month
- Set up automated invoice generation if possible
- Review reports regularly to track performance
- Send reminders before due dates
- Keep tenant information updated

### For Admins

- Customize reminder templates to match your brand
- Monitor dashboard for system health
- Regularly export data for backup
- Review overdue invoices and take action
- Keep all user roles properly assigned

---

## Troubleshooting

### Can't Pay Invoice

- Check if invoice is already paid
- Verify you're logged in as the correct tenant
- Ensure invoice belongs to your account
- Check browser console for errors

### Invoice Not Showing

- Verify invoice status (draft invoices may not show to tenants)
- Check if invoice is assigned to your tenant account
- Contact property manager/owner

### Payment Not Recorded

- Check payment status on Flutterwave
- Verify webhook is configured correctly
- Contact support with transaction reference

### Can't Access Feature

- Verify your user role has permission
- Check if feature is enabled
- Contact administrator

---

## Support

For issues or questions:
1. Check this user guide
2. Review troubleshooting section
3. Contact your property manager/owner
4. For admins: Check Laravel logs

---

**Last Updated**: December 2024

