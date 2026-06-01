<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\CMS\Actions\SyncDonationFundsAction;
use App\Domains\CMS\Actions\SyncMembershipsAction;
use App\Domains\CMS\Models\DonationFund;
use App\Domains\CMS\Models\Membership;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

#[Signature('ticketing:sync-journeys')]
#[Description('Sync donation funds and memberships for CMS-managed customer journeys.')]
class SyncTicketingJourneysCommand extends Command
{
    public function handle(
        SyncDonationFundsAction $syncDonationFunds,
        SyncMembershipsAction $syncMemberships,
    ): int {
        $this->emitStaleDataAlertIfNeeded();

        $fundCount = $this->executeWithRetry(fn (): int => $syncDonationFunds->execute());
        $membershipCount = $this->executeWithRetry(fn (): int => $syncMemberships->execute());

        $this->components->info("Synced {$fundCount} donation funds and {$membershipCount} memberships.");

        return self::SUCCESS;
    }

    /**
     * @param  callable(): int  $callback
     */
    private function executeWithRetry(callable $callback): int
    {
        $attempts = max((int) config('ticketing.journeys.retry_attempts', 3), 1);
        /** @var list<int> $backoffSeconds */
        $backoffSeconds = config('ticketing.journeys.retry_backoff_seconds', [10, 30]);
        $backoffSeconds = array_values(array_filter($backoffSeconds, static fn (int $seconds): bool => $seconds > 0));

        $lastException = null;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                return $callback();
            } catch (Throwable $exception) {
                $lastException = $exception;

                if ($attempt === $attempts) {
                    throw $exception;
                }

                $sleepSeconds = $backoffSeconds[$attempt - 1] ?? end($backoffSeconds) ?: 1;
                sleep($sleepSeconds);
            }
        }

        throw $lastException ?? new \RuntimeException('Journey sync failed without an exception.');
    }

    private function emitStaleDataAlertIfNeeded(): void
    {
        $staleAfterMinutes = max((int) config('ticketing.journeys.stale_after_minutes', 180), 1);
        $cooldownMinutes = max((int) config('ticketing.journeys.stale_alert_cooldown_minutes', 60), 1);

        $latestFundSync = DonationFund::query()->max('synced_at');
        $latestMembershipSync = Membership::query()->max('synced_at');
        $latestSync = collect([$latestFundSync, $latestMembershipSync])->filter()->max();

        if ($latestSync !== null && CarbonImmutable::parse($latestSync)->gt(now()->subMinutes($staleAfterMinutes))) {
            return;
        }

        $provider = (string) config('ticketing.default', 'unknown');
        $alertKey = "ticketing:journeys:stale-alert:{$provider}";

        if (! Cache::add($alertKey, true, now()->addMinutes($cooldownMinutes))) {
            return;
        }

        Log::warning('Ticketing journey data appears stale before sync run.', [
            'provider' => $provider,
            'stale_after_minutes' => $staleAfterMinutes,
            'latest_synced_at' => $latestSync,
        ]);
    }
}
