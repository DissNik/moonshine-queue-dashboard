<?php

namespace DissNik\MoonShineQueueDashboard\DTOs;

use Illuminate\Support\Collection;

class QueueMonitorDTO
{
    public function __construct(
        public string $connection,
        public string $queue,
        public int $size,
        public int $pending,
        public int $delayed,
        public int $reserved,
        public ?string $oldest_pending,
        public string $status
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            connection: $data['connection'],
            queue: $data['queue'],
            size: $data['size'],
            pending: $data['pending'],
            delayed: $data['delayed'],
            reserved: $data['reserved'],
            oldest_pending: $data['oldest_pending'],
            status: $data['status']
        );
    }

    public static function collectionFromArray(array $queuesData): Collection
    {
        return collect($queuesData)->map(function ($queueData) {
            return self::fromArray($queueData);
        });
    }
}
