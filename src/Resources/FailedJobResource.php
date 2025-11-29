<?php

declare(strict_types=1);

namespace DissNik\MoonShineQueueDashboard\Resources;

use Closure;
use DissNik\MoonShineQueueDashboard\Models\FailedJob;

use DissNik\MoonShineQueueDashboard\Services\QueueService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\Enums\Action;
use MoonShine\Laravel\Http\Responses\MoonShineJsonResponse;
use MoonShine\Laravel\MoonShineRequest;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\AlpineJs;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Support\Enums\ClickAction;
use MoonShine\Support\Enums\JsEvent;
use MoonShine\Support\Enums\ToastType;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Fields\DateRange;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use Throwable;

/**
 * @extends ModelResource<FailedJob>
 */
#[Icon('star')]
class FailedJobResource extends ModelResource
{
    protected string $model = FailedJob::class;
    protected string $title = 'Failed Job';
    protected string $column = 'UUID';
    protected bool $isAsync = true;
    protected bool $detailInModal = true;
    protected int $itemsPerPage = 20;
    protected ?ClickAction $clickAction = ClickAction::DETAIL;

    protected QueueService $queueService;

    public function __construct(
        CoreContract $core
    )
    {
        parent::__construct($core);

        $this->queueService = app(QueueService::class);
    }

    public function getTitle(): string
    {
        return __('moonshine-queue-dashboard::dashboard.failed_jobs');
    }

    protected function activeActions(): ListOf
    {
        return new ListOf(Action::class, [
            Action::VIEW,
        ]);
    }

    protected function detailFields(): iterable
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

    protected function indexFields(): iterable
    {
        return [
            ID::make(
                __('moonshine-queue-dashboard::dashboard.uuid'),
                'uuid'
            )
                ->customWrapperAttributes(['class' => 'w-1 font-bold'])
                ->sortable(),
            Text::make(
                __('moonshine-queue-dashboard::dashboard.queue'),
                'queue',
                fn($item) => Str::title(str_replace(['-', '_'], ' ', $item->queue))
            )
                ->badge()
                ->sortable(),
            Text::make(
                __('moonshine-queue-dashboard::dashboard.failed_ago'),
                'failed_ago',
                fn($item) => $item->failed_ago
            )
                ->sortable(),
            Text::make(
                __('moonshine-queue-dashboard::dashboard.exception_message'),
                'exception_message',
                fn($item) => $item->exception_message
            ),
        ];
    }

    /**
     * @throws Throwable
     */
    protected function topButtons(): ListOf
    {
        return parent::topButtons()
            ->add(
                ActionButton::make(__('moonshine-queue-dashboard::dashboard.retry_all'))
                    ->method(
                        'retryAll',
                        events: $this->getListEvent(),
                        resource: app(FailedJobResource::class)
                    )
                    ->icon('arrow-path-rounded-square')
                    ->success(),
            )
            ->add(
                ActionButton::make(__('moonshine-queue-dashboard::dashboard.clear_all'))
                    ->method(
                        'flush',
                        events: $this->getListEvent(),
                        resource: app(FailedJobResource::class)
                    )
                    ->icon('trash')
                    ->error()
                    ->withConfirm(),
            )
            ->add(
                ActionButton::make('')
                    ->customView(
                        'moonshine-queue-dashboard::components.refresh-button',
                        [
                            'autoRefresh' => config('moonshine-queue-dashboard.auto_refresh'),
                            'refreshInterval' => config('moonshine-queue-dashboard.refresh_interval'),
                        ],
                    )
            );
    }

    /**
     * @throws Throwable
     */
    protected function indexButtons(): ListOf
    {
        return parent::indexButtons()
            ->add(
                ActionButton::make('')
                    ->method(
                        'retry',
                        events: $this->getListEvent(),
                        resource: app(FailedJobResource::class)
                    )
                    ->async()
                    ->icon('arrow-path')
                    ->success()
            )
            ->add(
                ActionButton::make('')
                    ->method(
                        'moonshine-queue-dashboard::dashboard.forget',
                        events: $this->getListEvent(),
                        resource: app(FailedJobResource::class)
                    )
                    ->icon('trash')
                    ->error()
                    ->withConfirm()
            );
    }

    protected function filters(): iterable
    {
        return [
            DateRange::make(__('moonshine-queue-dashboard::dashboard.dates'), 'failed_at')
                ->withTime(),
            Textarea::make(
                __('moonshine-queue-dashboard::dashboard.message'),
                'exception',
            ),
        ];
    }

    private function getListEvent(): array
    {
        return [
            AlpineJs::event(JsEvent::TABLE_UPDATED, $this->getListComponentName()),
            AlpineJs::event(JsEvent::FRAGMENT_UPDATED, 'queue_metrics'),
        ];
    }

    protected function queryTags(): array
    {
        $queues = config('moonshine-queue-dashboard.queues', ['default']);

        return collect($queues)
            ->map(fn($queue) => QueryTag::make(
                Str::upper(str_replace(['-', '_'], ' ', $queue)),
                fn(Builder $query) => $query->where('queue', $queue)
            ))
            ->prepend(
                QueryTag::make(
                    __('moonshine-queue-dashboard::dashboard.all'),
                    fn(Builder $query) => $query
                )->default()
            )
            ->values()
            ->all();
    }

    protected function resolveOrder(string $column, string $direction, ?Closure $callback): static
    {
        if ($callback instanceof Closure) {
            $callback($this->newQuery(), $column, $direction);
        } elseif($column === 'failed_ago') {
            $this->newQuery()
                ->orderBy('failed_at', $direction);
        } else {
            $this->newQuery()->orderBy($column, $direction);
        }

        return $this;
    }

    public function retry(MoonShineRequest $request): MoonShineJsonResponse
    {
        $item = $request->getResource()->getItemOrInstance();

        try {
            $this->queueService->callQueueRetry($item->uuid);

            return MoonShineJsonResponse::make()
                ->toast(__('moonshine-queue-dashboard::messages.job_has_been_queued_for_retry'), ToastType::SUCCESS);
        } catch (Exception) {
            return MoonShineJsonResponse::make()
                ->toast(__('moonshine-queue-dashboard::messages.failed_to_retry_job'), ToastType::ERROR);
        }
    }

    public function forget(MoonShineRequest $request): MoonShineJsonResponse
    {
        $item = $request->getResource()->getItemOrInstance();

        try {
            $this->queueService->callQueueForget($item->uuid);

            return MoonShineJsonResponse::make()
                ->toast(__('moonshine-queue-dashboard::messages.job_has_been_forget'), ToastType::SUCCESS);
        } catch (Exception) {
            return MoonShineJsonResponse::make()
                ->toast(__('moonshine-queue-dashboard::messages.failed_job_has_been_forge'), ToastType::SUCCESS);
        }
    }

    public function retryAll(): MoonShineJsonResponse
    {
        try {
            $this->queueService->callQueueRetryAll();

            return MoonShineJsonResponse::make()
                ->toast(__('moonshine-queue-dashboard::messages.jobs_has_been_queued_for_retry'), ToastType::SUCCESS);
        } catch (Exception) {
            return MoonShineJsonResponse::make()
                ->toast(__('moonshine-queue-dashboard::messages.failed_to_retry_jobs'), ToastType::ERROR);
        }
    }

    public function flush(): MoonShineJsonResponse
    {
        try {
            $this->queueService->callQueueFlush();

            return MoonShineJsonResponse::make()
                ->toast(__('moonshine-queue-dashboard::messages.failed_jobs_have_been_deleted'), ToastType::SUCCESS);
        } catch (Exception) {
            return MoonShineJsonResponse::make()
                ->toast('moonshine-queue-dashboard::messages.error_deleting_failed_jobs', ToastType::ERROR);
        }
    }
}
