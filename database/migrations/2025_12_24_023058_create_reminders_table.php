<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('reminders');
        
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('cascade');
            $table->enum('type', ['payment_due', 'payment_overdue', 'lease_expiry', 'custom'])->default('payment_due');
            $table->string('subject');
            $table->text('message');
            $table->date('reminder_date');
            $table->enum('status', ['pending', 'sent', 'cancelled'])->default('pending');
            $table->enum('channel', ['email', 'sms', 'both'])->default('email');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
