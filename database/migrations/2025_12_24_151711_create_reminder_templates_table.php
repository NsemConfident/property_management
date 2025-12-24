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
        Schema::create('reminder_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['payment_due', 'payment_overdue', 'lease_expiry', 'custom'])->default('custom');
            $table->string('subject');
            $table->text('message');
            $table->boolean('is_active')->default(true);
            $table->integer('days_before')->nullable()->comment('Days before due date/expiry (for payment_due and lease_expiry)');
            $table->text('variables_help')->nullable()->comment('Help text explaining available variables');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminder_templates');
    }
};
