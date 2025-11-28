<?php

namespace DissNik\MoonShineQueueDashboard\Services;

use DissNik\MoonShineQueueDashboard\DTOs\QueueMonitorDTO;
use DissNik\MoonShineQueueDashboard\Models\FailedJob;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Contracts\Queue\Factory as QueueFactory;

readonly class QueueService
{
    public function __construct(
        private QueueFactory $queue
    ) {}

    public function getQueuesData(): Collection
    {
        $queues = config('moonshine-queue-dashboard.queues', ['default']);
        $queueString = is_array($queues) ? implode(',', $queues) : $queues;

        Artisan::call('queue:monitor', [
            'queues' => $queueString,
            '--json' => true,
        ]);

        $data = json_decode(Artisan::output(), true);

        return QueueMonitorDTO::collectionFromArray($data);
    }

    public function getQueueSize(): int
    {
        try {
            $queues = config('moonshine-queue-dashboard.queues', ['default']);
            $connection = $this->queue->connection();

            return collect($queues)->sum(fn($queue) => $connection->size($queue));
        } catch (Exception) {
            return 0;
        }
    }

    public function callQueueRetryAll(): void
    {
        Artisan::call('queue:retry all');
    }

    public function callQueueFlush(): void
    {
        Artisan::call('queue:flush');
    }

    public function callQueueForget(string $uuid): void
    {
        Artisan::call('queue:forget', ['id' => $uuid]);
    }

    public function callQueueRetry(string $uuid): void
    {
        Artisan::call('queue:retry', ['id' => $uuid]);
    }

    public function getFailedJobsCount(): int
    {
        return FailedJob::query()
            ->whereIn('queue', config('moonshine-queue-dashboard.queues'))
            ->count();
    }
}
