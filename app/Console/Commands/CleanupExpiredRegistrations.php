<?php

namespace App\Console\Commands;

use App\Models\Central\Registration;
use App\Enums\Central\RegistrationStatus;
use Illuminate\Console\Command;

class CleanupExpiredRegistrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'registrations:cleanup {--hours=24 : Hours after which pending registrations expire}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark expired pending registrations as expired (preserves leads for follow-up)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = (int) $this->option('hours');
        $cutoff = now()->subHours($hours);

        $expiredCount = Registration::whereIn('status', [RegistrationStatus::PENDING, RegistrationStatus::CONFIRMED])
            ->where('token_expires_at', '<', now())
            ->count();

        if ($expiredCount === 0) {
            $this->info('No expired registrations found.');
            return 0;
        }

        $this->info("Found {$expiredCount} registrations with expired tokens.");

        if ($this->confirm('Do you want to mark these registrations as expired?')) {
            $updated = Registration::whereIn('status', [RegistrationStatus::PENDING, RegistrationStatus::CONFIRMED])
                ->where('token_expires_at', '<', now())
                ->update(['status' => RegistrationStatus::EXPIRED]);

            $this->info("✅ Marked {$updated} registrations as expired (preserved for follow-up campaigns).");
            $this->info("💡 You can now implement follow-up email campaigns for these leads.");
        } else {
            $this->info('Cleanup cancelled.');
        }

        return 0;
    }
}