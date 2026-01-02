<?php

declare(strict_types=1);

namespace DissNik\MoonShineQueueDashboard\Resources;

use DissNik\MoonShineQueueDashboard\Models\FailedJob;
use DissNik\MoonShineQueueDashboard\Resources\Pages\FailedJobDetailPage;
use DissNik\MoonShineQueueDashboard\Resources\Pages\FailedJobIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\ListOf;

/**
 * @extends ModelResource<FailedJob>
 */
class FailedJobResource extends ModelResource
{
    protected string $model = FailedJob::class;

    protected string $title = 'Failed Job';

    protected string $column = 'UUID';

    protected bool $detailInModal = true;

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            FailedJobIndexPage::class,
            FailedJobDetailPage::class,
        ];
    }

    /**
     * @return ListOf<Action>
     */
    protected function activeActions(): ListOf
    {
        return new ListOf(Action::class, [
            Action::VIEW,
        ]);
    }
}
