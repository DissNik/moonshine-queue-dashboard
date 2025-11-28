<?php

namespace DissNik\MoonShineQueueDashboard\Components;

use DissNik\MoonShineQueueDashboard\DTOs\QueueMonitorDTO;
use MoonShine\UI\Components\MoonShineComponent;

class Metric extends MoonShineComponent
{
    protected string $view = 'moonshine-queue-dashboard::components.metric';

    public function __construct(
        protected QueueMonitorDTO $metric
    )
    {
        parent::__construct();
    }

    public function viewData(): array
    {
        return [
            'metric' => $this->metric,
        ];
    }
}
