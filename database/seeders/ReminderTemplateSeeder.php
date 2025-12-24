<?php

namespace Database\Seeders;

use App\Models\ReminderTemplate;
use Illuminate\Database\Seeder;

class ReminderTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Payment Due Template
        ReminderTemplate::firstOrCreate(
            ['name' => 'Default Payment Due Reminder'],
            [
                'type' => 'payment_due',
                'subject' => 'Payment Reminder: Invoice {{invoice_number}} Due Soon',
                'message' => "Dear {{tenant_name}},\n\nThis is a friendly reminder that your rent payment is due in {{days_until_due}} day(s).\n\nInvoice Details:\nInvoice Number: {{invoice_number}}\nProperty: {{property_name}}\nUnit: {{unit_number}}\nAmount Due: {{amount_balance}}\nDue Date: {{due_date}}\n\nPlease ensure payment is made before the due date to avoid any late fees.\n\nThank you for your prompt attention to this matter.\n\nBest regards,\nProperty Management Team",
                'is_active' => true,
                'days_before' => 3,
                'variables_help' => ReminderTemplate::getAvailableVariables('payment_due'),
            ]
        );

        // Payment Overdue Template
        ReminderTemplate::firstOrCreate(
            ['name' => 'Default Payment Overdue Reminder'],
            [
                'type' => 'payment_overdue',
                'subject' => 'URGENT: Overdue Payment - Invoice {{invoice_number}}',
                'message' => "Dear {{tenant_name}},\n\nURGENT: Your rent payment is now {{days_overdue}} day(s) overdue.\n\nInvoice Details:\nInvoice Number: {{invoice_number}}\nProperty: {{property_name}}\nUnit: {{unit_number}}\nAmount Overdue: {{amount_balance}}\nDue Date: {{due_date}}\n\nPlease make payment immediately to avoid further action. Late fees may apply.\n\nIf you have already made this payment, please contact us immediately.\n\nBest regards,\nProperty Management Team",
                'is_active' => true,
                'days_before' => null,
                'variables_help' => ReminderTemplate::getAvailableVariables('payment_overdue'),
            ]
        );

        // Lease Expiry Template
        ReminderTemplate::firstOrCreate(
            ['name' => 'Default Lease Expiry Reminder'],
            [
                'type' => 'lease_expiry',
                'subject' => 'Lease Expiry Reminder - {{property_name}}',
                'message' => "Dear {{tenant_name}},\n\nThis is a reminder that your lease agreement will expire in {{days_until_expiry}} day(s).\n\nLease Details:\nProperty: {{property_name}}\nUnit: {{unit_number}}\nLease End Date: {{lease_end_date}}\nMonthly Rent: {{monthly_rent}}\n\nPlease contact us to discuss lease renewal or move-out procedures.\n\nBest regards,\nProperty Management Team",
                'is_active' => true,
                'days_before' => 30,
                'variables_help' => ReminderTemplate::getAvailableVariables('lease_expiry'),
            ]
        );
    }
}

