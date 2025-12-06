<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RunExpiryAlertsDaemon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:expiry:daemon {--interval=15 : Interval in seconds between runs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daemon that runs alerts:expiry on a fixed interval (default 15s)';

    public function handle()
    {
        $interval = (int) $this->option('interval') ?: 1;
        $this->info("Starting expiry alerts daemon (interval={$interval}s). Press Ctrl+C to stop.");

        // Resolve the existing command class so we reuse its logic
        $worker = app(SendExpiryAlerts::class);

        while (true) {
            try {
                $worker->handle();
            } catch (\Throwable $e) {
                $this->error('Expiry daemon error: ' . $e->getMessage());
                Log::error('Expiry daemon error: ' . $e->getMessage(), ['exception' => $e]);
            }

            // Sleep in 1-second chunks so Ctrl+C is more responsive
            $slept = 0;
            while ($slept < $interval) {
                sleep(1);
                $slept++;
            }
        }

        return 0;
    }
}
