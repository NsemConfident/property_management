<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Property;
use App\Models\Unit;
use App\Models\Tenant;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PropertyManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '+2348012345677',
                'address' => 'Lagos, Nigeria',
                'email_verified_at' => now(),
            ]
        );

        // Create Owner
        $owner = User::firstOrCreate(
            ['email' => 'owner@example.com'],
            [
                'name' => 'John Owner',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'phone' => '+2348012345678',
                'address' => 'Lagos, Nigeria',
                'email_verified_at' => now(),
            ]
        );

        // Create Manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Jane Manager',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'phone' => '+2348012345679',
                'address' => 'Lagos, Nigeria',
                'email_verified_at' => now(),
            ]
        );

        // Create Tenant Users
        $tenant1 = User::firstOrCreate(
            ['email' => 'tenant1@example.com'],
            [
                'name' => 'Mike Tenant',
                'password' => Hash::make('password'),
                'role' => 'tenant',
                'phone' => '+2348012345680',
                'address' => 'Lagos, Nigeria',
                'email_verified_at' => now(),
            ]
        );

        $tenant2 = User::firstOrCreate(
            ['email' => 'tenant2@example.com'],
            [
                'name' => 'Sarah Tenant',
                'password' => Hash::make('password'),
                'role' => 'tenant',
                'phone' => '+2348012345681',
                'address' => 'Lagos, Nigeria',
                'email_verified_at' => now(),
            ]
        );

        // Create Property
        $property = Property::firstOrCreate(
            [
                'name' => 'Sunset Apartments',
                'owner_id' => $owner->id,
            ],
            [
                'manager_id' => $manager->id,
                'description' => 'A modern residential apartment complex in Lagos',
                'address' => '123 Victoria Island',
                'city' => 'Lagos',
                'state' => 'Lagos',
                'country' => 'Nigeria',
                'postal_code' => '101001',
                'type' => 'apartment',
                'total_units' => 10,
                'status' => 'active',
            ]
        );

        // Create Units
        $unit1 = Unit::firstOrCreate(
            [
                'property_id' => $property->id,
                'unit_number' => 'A101',
            ],
            [
                'unit_type' => '2-bedroom',
                'monthly_rent' => 150000.00,
                'deposit' => 300000.00,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'square_feet' => 1200.00,
                'status' => 'occupied',
                'description' => 'Spacious 2-bedroom apartment with balcony',
            ]
        );

        $unit2 = Unit::firstOrCreate(
            [
                'property_id' => $property->id,
                'unit_number' => 'A102',
            ],
            [
                'unit_type' => '1-bedroom',
                'monthly_rent' => 100000.00,
                'deposit' => 200000.00,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'square_feet' => 800.00,
                'status' => 'occupied',
                'description' => 'Cozy 1-bedroom apartment',
            ]
        );

        $unit3 = Unit::firstOrCreate(
            [
                'property_id' => $property->id,
                'unit_number' => 'A103',
            ],
            [
                'unit_type' => '3-bedroom',
                'monthly_rent' => 200000.00,
                'deposit' => 400000.00,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'square_feet' => 1500.00,
                'status' => 'available',
                'description' => 'Luxury 3-bedroom apartment',
            ]
        );

        // Create Tenants
        $tenantRecord1 = Tenant::firstOrCreate(
            [
                'user_id' => $tenant1->id,
                'unit_id' => $unit1->id,
            ],
            [
                'lease_start_date' => now()->subMonths(6),
                'lease_end_date' => now()->addMonths(6),
                'monthly_rent' => 150000.00,
                'lease_status' => 'active',
                'emergency_contact_name' => 'Emergency Contact 1',
                'emergency_contact_phone' => '+2348012345682',
                'notes' => 'Good tenant, pays on time',
            ]
        );

        $tenantRecord2 = Tenant::firstOrCreate(
            [
                'user_id' => $tenant2->id,
                'unit_id' => $unit2->id,
            ],
            [
                'lease_start_date' => now()->subMonths(3),
                'lease_end_date' => now()->addMonths(9),
                'monthly_rent' => 100000.00,
                'lease_status' => 'active',
                'emergency_contact_name' => 'Emergency Contact 2',
                'emergency_contact_phone' => '+2348012345683',
                'notes' => 'New tenant',
            ]
        );

        // Create Invoices
        $invoice1 = Invoice::create([
            'tenant_id' => $tenantRecord1->id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'invoice_date' => now()->startOfMonth(),
            'due_date' => now()->startOfMonth()->addDays(7),
            'amount' => 150000.00,
            'paid_amount' => 150000.00,
            'balance' => 0.00,
            'status' => 'paid',
            'description' => 'Monthly rent for ' . now()->format('F Y'),
            'line_items' => [
                ['description' => 'Monthly Rent', 'amount' => 150000.00],
            ],
            'paid_at' => now()->startOfMonth()->addDays(2),
        ]);

        $invoice2 = Invoice::create([
            'tenant_id' => $tenantRecord1->id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'invoice_date' => now()->startOfMonth()->addMonth(),
            'due_date' => now()->startOfMonth()->addMonth()->addDays(7),
            'amount' => 150000.00,
            'paid_amount' => 0.00,
            'balance' => 150000.00,
            'status' => 'sent',
            'description' => 'Monthly rent for ' . now()->addMonth()->format('F Y'),
            'line_items' => [
                ['description' => 'Monthly Rent', 'amount' => 150000.00],
            ],
        ]);

        $invoice3 = Invoice::create([
            'tenant_id' => $tenantRecord2->id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'invoice_date' => now()->startOfMonth(),
            'due_date' => now()->startOfMonth()->addDays(7),
            'amount' => 100000.00,
            'paid_amount' => 0.00,
            'balance' => 100000.00,
            'status' => 'overdue',
            'description' => 'Monthly rent for ' . now()->format('F Y'),
            'line_items' => [
                ['description' => 'Monthly Rent', 'amount' => 100000.00],
            ],
        ]);

        // Create Payments
        Payment::create([
            'tenant_id' => $tenantRecord1->id,
            'invoice_id' => $invoice1->id,
            'amount' => 150000.00,
            'payment_date' => now()->startOfMonth()->addDays(2),
            'payment_method' => 'bank_transfer',
            'status' => 'completed',
            'transaction_reference' => 'TXN-' . now()->timestamp . '-001',
            'receipt_number' => 'RCP-' . now()->timestamp . '-001',
            'notes' => 'Payment received via bank transfer',
        ]);

        $this->command->info('Property Management test data seeded successfully!');
        $this->command->info('Login credentials:');
        $this->command->info('Admin: admin@example.com / password (Access Filament at /admin)');
        $this->command->info('Owner: owner@example.com / password');
        $this->command->info('Manager: manager@example.com / password');
        $this->command->info('Tenant 1: tenant1@example.com / password');
        $this->command->info('Tenant 2: tenant2@example.com / password');
    }
}
