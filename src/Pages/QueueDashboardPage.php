<?php

namespace DissNik\MoonShineQueueDashboard\Pages;

use DissNik\MoonShineQueueDashboard\Components\FailedJobs;
use DissNik\MoonShineQueueDashboard\Components\QueueMetrics;
use MoonShine\Laravel\Components\Fragment;
use MoonShine\Laravel\Pages\Page;
use MoonShine\UI\Components\Layout\Divider;

class QueueDashboardPage extends Page
{
    public function getTitle(): string
    {
        return __('moonshine-queue-dashboard::dashboard.queue_dashboard');
    }

    public function getBreadcrumbs(): array
    {
        return [
            '#' => $this->getTitle()
        ];
    }

    public function components(): iterable
    {
        return [
            Fragment::make([
                QueueMetrics::make(),
            ])
                ->name('queue_metrics'),

            Divider::make(__('moonshine-queue-dashboard::dashboard.failed_jobs'))
                ->centered(),

            FailedJobs::make(),
        ];
    }
}
