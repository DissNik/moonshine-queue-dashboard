<?php

namespace DissNik\MoonShineQueueDashboard\Components;

use DissNik\MoonShineQueueDashboard\Resources\FailedJobResource;
use MoonShine\UI\Components\MoonShineComponent;

class FailedJobs extends MoonShineComponent
{
    protected string $view = 'moonshine-queue-dashboard::components.failed-jobs';

    public function viewData(): array
    {
        return [
            'components' => app(FailedJobResource::class)->getIndexPage()->getComponents(),
        ];
    }
}
