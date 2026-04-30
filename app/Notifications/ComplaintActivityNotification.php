<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComplaintActivityNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $headline,
        public string $body,
        public string $url,
        public array $payload = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->headline)
            ->greeting('Hello '.($notifiable->username ?? 'there').',')
            ->line($this->body)
            ->action('Open complaint thread', $this->url)
            ->line('Please review the complaint thread and respond when needed.');
    }

    public function toDatabase(object $notifiable): array
    {
        return array_merge([
            'headline' => $this->headline,
            'body'     => $this->body,
            'url'      => $this->url,
        ], $this->payload);
    }
}
