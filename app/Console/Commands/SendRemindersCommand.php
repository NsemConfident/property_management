<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Illuminate\Console\Command;

class SendRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send 
                            {--create : Create new reminders before sending}
                            {--days-before=3 : Days before due date to create payment reminders}
                            {--lease-days=30 : Days before lease expiry to create reminders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send pending payment and lease reminders to tenants';

    /**
     * Execute the console command.
     */
    public function handle(ReminderService $reminderService): int
    {
        $this->info('Starting reminder process...');

        // Create new reminders if requested
        if ($this->option('create')) {
            $this->info('Creating new reminders...');
            
            // Create payment due reminders
            $daysBefore = (int) $this->option('days-before');
            $createdDue = $reminderService->createRemindersForDueInvoices($daysBefore);
            $this->info("Created {$createdDue} payment due reminders.");

            // Create overdue reminders
            $createdOverdue = $reminderService->createRemindersForOverdueInvoices();
            $this->info("Created {$createdOverdue} overdue payment reminders.");

            // Create lease expiry reminders
            $leaseDays = (int) $this->option('lease-days');
            $createdLease = $reminderService->createRemindersForExpiringLeases($leaseDays);
            $this->info("Created {$createdLease} lease expiry reminders.");
        }

        // Send pending reminders
        $this->info('Sending pending reminders...');
        $result = $reminderService->sendPendingReminders();

        $sentCount = count($result['sent']);
        $failedCount = count($result['failed']);

        $this->info("Sent {$sentCount} reminders successfully.");

        if ($failedCount > 0) {
            $this->warn("Failed to send {$failedCount} reminders.");
            foreach ($result['failed'] as $failure) {
                $this->error("  - Reminder #{$failure['reminder']->id}: {$failure['error']}");
            }
        }

        $this->info('Reminder process completed.');

        return Command::SUCCESS;
    }
}
