<?php

declare(strict_types=1);

namespace App\Domains\Events\Jobs;

use App\Domains\Events\Actions\SyncCatalogueAction;
use App\Domains\Events\Enums\SyncRunStatus;
use App\Domains\Events\Models\SyncRun;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\UniqueFor;
use Illuminate\Support\Str;
use Throwable;

#[UniqueFor(600)]
final class SyncTicketingCatalogue implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 50;

    /**
     * @var list<int>
     */
    public array $backoff = [10, 30, 60];

    public function __construct(public readonly int $syncRunId) {}

    public function handle(SyncCatalogueAction $action): void
    {
        $syncRun = SyncRun::findOrFail($this->syncRunId);

        if (
            $syncRun->status === SyncRunStatus::Failed
            && is_array($syncRun->context)
            && ($syncRun->context['reason'] ?? null) === 'active_run_timeout'
        ) {
            return;
        }

        $action->execute($syncRun);
    }

    public function uniqueId(): string
    {
        return 'ticketing:catalogue';
    }

    /**
     * @return list<string>
     */
    public function tags(): array
    {
        return [
            'ticketing',
            'provider:spektrix',
            'sync:catalogue',
            'sync-run:'.$this->syncRunId,
        ];
    }

    public function failed(?Throwable $exception): void
    {
        $syncRun = SyncRun::find($this->syncRunId);

        if ($syncRun === null || $syncRun->getRawOriginal('status') === SyncRunStatus::Succeeded->value) {
            return;
        }

        $syncRun->update([
            'status' => SyncRunStatus::Failed,
            'finished_at' => now(),
            'error_message' => $exception === null
                ? 'Catalogue sync job failed without an exception message.'
                : Str::limit($exception->getMessage(), 1000),
        ]);
    }
}
