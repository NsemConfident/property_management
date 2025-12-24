<?php

namespace App\Notifications;

use App\Models\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Reminder $reminder
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $reminder = $this->reminder;
        $tenant = $reminder->tenant;
        $invoice = $reminder->invoice;

        $mailMessage = (new MailMessage)
            ->subject($reminder->subject)
            ->greeting("Hello {$notifiable->name},")
            ->line($reminder->message);

        // Add invoice details if available
        if ($invoice) {
            $mailMessage->line("**Invoice Number:** {$invoice->invoice_number}")
                ->line("**Amount Due:** ₦" . number_format($invoice->balance, 2))
                ->line("**Due Date:** {$invoice->due_date->format('F j, Y')}");
        }

        // Add action button if invoice exists
        if ($invoice) {
            $mailMessage->action('View Invoice', url('/invoices/' . $invoice->id));
        }

        $mailMessage->line('Thank you for your attention to this matter.');

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'reminder_id' => $this->reminder->id,
            'type' => $this->reminder->type,
            'subject' => $this->reminder->subject,
            'message' => $this->reminder->message,
        ];
    }
}

