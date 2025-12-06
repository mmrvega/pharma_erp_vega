<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Purchase;
use App\Models\User;
use App\Notifications\ExpiryAlertNotification;
use Carbon\Carbon;

class SendExpiryAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications for purchases that are expired or nearing expiry based on expiry_alert_days';

    public function handle()
    {
        $now = Carbon::now()->startOfDay();

        // Find purchases that have expiry_alert_days set and whose expiry_date is within the alert window
        $purchases = Purchase::whereNotNull('expiry_alert_days')
            ->where('expiry_alert_days', '>', 0)
            ->get()
            ->filter(function($purchase) use ($now) {
                $expiry = Carbon::parse($purchase->expiry_date)->startOfDay();
                $alertAt = $expiry->copy()->subDays($purchase->expiry_alert_days);
                // Send if today is >= alertAt and <= expiry (or already expired)
                return $now->gte($alertAt);
            });

        if ($purchases->isEmpty()) {
            $this->info('No expiry alerts to send.');
            return 0;
        }

        $users = User::get();

        foreach ($purchases as $purchase) {
            foreach ($users as $user) {
                $user->notify(new ExpiryAlertNotification($purchase));
            }
            $this->info("Notified users about purchase id {$purchase->id} ({$purchase->product})");
        }

        return 0;
    }
}
