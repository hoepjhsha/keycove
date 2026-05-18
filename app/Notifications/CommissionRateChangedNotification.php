<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommissionRateChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $oldRate,
        public string $newRate,
    ) {}

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
        return (new MailMessage)
            ->subject('KeyCove commission rate updated')
            ->greeting('Hello '.($notifiable->username ?? 'seller').',')
            ->line("The seller commission rate has changed from {$this->oldRate}% to {$this->newRate}%.")
            ->line('This rate applies to newly completed seller orders after the update.')
            ->action('Open seller dashboard', route('seller.dashboard.index'))
            ->line('Please review your listing margins if needed.');
    }
}
