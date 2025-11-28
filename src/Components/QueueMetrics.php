<?php

namespace DissNik\MoonShineQueueDashboard\Components;

use DissNik\MoonShineQueueDashboard\Services\QueueService;
use MoonShine\UI\Components\Metrics\Wrapped\ValueMetric;
use MoonShine\UI\Components\MoonShineComponent;

class QueueMetrics extends MoonShineComponent
{
    protected string $view = 'moonshine-queue-dashboard::components.queue-metrics';

    protected QueueService $queueService;

    public function __construct()
    {
        parent::__construct();

        $this->queueService = app(QueueService::class);
    }

    public function viewData(): array
    {
        return [
            'generalMetrics' => $this->getGeneralMetrics(),
            'queueMetrics' => $this->getQueueMetrics(),
        ];
    }

    protected function getGeneralMetrics(): array
    {
        return [
            ValueMetric::make(__('moonshine-queue-dashboard::dashboard.total_jobs'))
                ->value($this->queueService->getQueueSize())
                ->icon('document-text')
                ->columnSpan(6),

            ValueMetric::make(__('moonshine-queue-dashboard::dashboard.failed_jobs'))
                ->value($this->queueService->getFailedJobsCount())
                ->icon('exclamation-triangle')
                ->columnSpan(6),
        ];
    }

    protected function getQueueMetrics(): array
    {
        return collect($this->queueService->getQueuesData())
            ->map(fn($data) => Metric::make($data))
            ->toArray();
    }
}
