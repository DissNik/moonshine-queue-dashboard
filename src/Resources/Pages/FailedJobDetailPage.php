<?php

declare(strict_types=1);

namespace DissNik\MoonShineQueueDashboard\Resources\Pages;

use DissNik\MoonShineQueueDashboard\Resources\FailedJobResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;


/**
 * @extends DetailPage<FailedJobResource>
 */
class FailedJobDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(
                __('moonshine-queue-dashboard::dashboard.uuid'),
                'uuid'
            )
                ->customWrapperAttributes(['class' => 'font-bold']),
            Text::make(
                __('moonshine-queue-dashboard::dashboard.queue'),
                'queue',
                fn($item) => ucfirst(str_replace(['_', '-'], ' ', $item->queue))
            )
                ->badge(),
            Text::make(
                __('moonshine-queue-dashboard::dashboard.failed_at'),
                'failed_at'),
            Text::make(
                __('moonshine-queue-dashboard::dashboard.message'),
                'exception',
            )
                ->changePreview(function (?string $value) {
                    $parts = explode('Stack trace:', $value);
                    $stack = explode("\n", $parts[1]);

                    return view(
                        'moonshine-queue-dashboard::components.exception-message',
                        [
                            'error' => $parts[0],
                            'stack' => $stack
                        ]
                    );
                }),
        ];
    }
}
