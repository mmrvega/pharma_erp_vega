<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ExpiryAlertNotification extends Notification
{
    use Queueable;

    private $purchase;

    public function __construct($purchase)
    {
        $this->purchase = $purchase;
    }

    public function via($notifiable)
    {
        // Avoid sending mail by default to prevent SMTP issues on CLI runs.
        // We still persist the notification to the database and broadcast it.
        return ['database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        $url = url(route('purchases.edit', $this->purchase->id));
        $daysLeft = now()->diffInDays(
            \Carbon\Carbon::parse($this->purchase->expiry_date),
            false
        );

        $line = $daysLeft < 0 ? 'has already expired.' : "will expire in {$daysLeft} day(s).";

        return (new MailMessage)
            ->subject('Product expiry alert')
            ->greeting('Hello!')
            ->line("The product '{$this->purchase->product}' {$line}")
            ->action('View Purchase', $url)
            ->line('Please take appropriate action.');
    }

    public function toArray($notifiable)
    {
        $daysLeft = now()->diffInDays(\Carbon\Carbon::parse($this->purchase->expiry_date), false);
        return [
            'product_name' => $this->purchase->product,
            'expiry_date' => $this->purchase->expiry_date,
            'days_left' => $daysLeft,
            'image' => $this->purchase->image ?? null,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'product_name' => $this->purchase->product,
            'expiry_date' => $this->purchase->expiry_date,
            'days_left' => now()->diffInDays(\Carbon\Carbon::parse($this->purchase->expiry_date), false),
        ]);
    }
}
