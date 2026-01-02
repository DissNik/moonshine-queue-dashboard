<?php

declare(strict_types=1);

namespace DissNik\MoonShineQueueDashboard\Resources\Pages;

use DissNik\MoonShineQueueDashboard\Resources\FailedJobResource;
use DissNik\MoonShineQueueDashboard\Services\QueueService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Contracts\Core\DependencyInjection\CrudRequestContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Crud\JsonResponse;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Support\AlpineJs;
use MoonShine\Support\Attributes\AsyncMethod;
use MoonShine\Support\Enums\ClickAction;
use MoonShine\Support\Enums\JsEvent;
use MoonShine\Support\Enums\ToastType;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\DateRange;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;


/**
 * @extends IndexPage<FailedJobResource>
 */
class FailedJobIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    public function __construct(CoreContract $core, protected QueueService $queueService)
    {
        parent::__construct($core);
    }

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
                ->customWrapperAttributes(['class' => 'w-1 font-bold text-nowrap'])
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

    protected function buttons(): ListOf
    {
        return parent::buttons()
            ->add(
                ActionButton::make()
                    ->method(
                        'retry',
                        events: $this->getListEvent(),
                        page: app(FailedJobIndexPage::class),
                        resource: app(FailedJobResource::class)
                    )
                    ->class('btn-square')
                    ->async()
                    ->icon('arrow-path')
                    ->success()
            )
            ->add(
                ActionButton::make()
                    ->method(
                        'forget',
                        events: $this->getListEvent(),
                        page: app(FailedJobIndexPage::class),
                        resource: app(FailedJobResource::class)
                    )
                    ->class('btn-square')
                    ->icon('trash')
                    ->error()
                    ->withConfirm()
            );
    }

    protected function topLeftButtons(): ListOf
    {
        return parent::topLeftButtons()
            ->add(
                ActionButton::make(__('moonshine-queue-dashboard::dashboard.retry_all'))
                    ->method(
                        'retryAll',
                        events: $this->getListEvent(),
                        page: app(FailedJobIndexPage::class),
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
                        page: app(FailedJobIndexPage::class),
                        resource: app(FailedJobResource::class)
                    )
                    ->icon('trash')
                    ->error()
                    ->withConfirm(),
            )
            ->add(
                ActionButton::make()
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
     * @return list<FieldContract>
     */
    protected function filters(): iterable
    {
        return [
            DateRange::make(__('moonshine-queue-dashboard::dashboard.interval'), 'failed_at')
                ->withTime(),
            Textarea::make(
                __('moonshine-queue-dashboard::dashboard.exception_message'),
                'exception',
            ),
        ];
    }

    /**
     * @return list<QueryTag>
     */
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

    /**
     * @param  TableBuilder  $component
     *
     * @return TableBuilder
     */
    protected function modifyListComponent(ComponentContract $component): ComponentContract
    {
        return $component->clickAction(ClickAction::DETAIL)
            ->skeleton(false);
    }

    private function getListEvent(): array
    {
        return [
            AlpineJs::event(JsEvent::TABLE_UPDATED, $this->getListComponentName()),
            AlpineJs::event(JsEvent::FRAGMENT_UPDATED, 'queue_metrics'),
        ];
    }

    #[AsyncMethod]
    public function retry(CrudRequestContract $request): JsonResponse
    {
        $item = $request->getResource()->getItemOrInstance();

        try {
            $this->queueService->callQueueRetry($item->uuid);

            return JsonResponse::make()
                ->toast(__('moonshine-queue-dashboard::messages.job_has_been_queued_for_retry'), ToastType::SUCCESS);
        } catch (Exception) {
            return JsonResponse::make()
                ->toast(__('moonshine-queue-dashboard::messages.failed_to_retry_job'), ToastType::ERROR);
        }
    }

    #[AsyncMethod]
    public function forget(CrudRequestContract $request): JsonResponse
    {
        $item = $request->getResource()->getItemOrInstance();

        try {
            $this->queueService->callQueueForget($item->uuid);

            return JsonResponse::make()
                ->toast(__('moonshine-queue-dashboard::messages.job_has_been_forget'), ToastType::SUCCESS);
        } catch (Exception) {
            return JsonResponse::make()
                ->toast(__('moonshine-queue-dashboard::messages.failed_job_has_been_forge'), ToastType::SUCCESS);
        }
    }

    #[AsyncMethod]
    public function retryAll(): JsonResponse
    {
        try {
            $this->queueService->callQueueRetryAll();

            return JsonResponse::make()
                ->toast(__('moonshine-queue-dashboard::messages.jobs_has_been_queued_for_retry'), ToastType::SUCCESS);
        } catch (Exception) {
            return JsonResponse::make()
                ->toast(__('moonshine-queue-dashboard::messages.failed_to_retry_jobs'), ToastType::ERROR);
        }
    }

    #[AsyncMethod]
    public function flush(): JsonResponse
    {
        try {
            $this->queueService->callQueueFlush();

            return JsonResponse::make()
                ->toast(__('moonshine-queue-dashboard::messages.failed_jobs_have_been_deleted'), ToastType::SUCCESS);
        } catch (Exception) {
            return JsonResponse::make()
                ->toast('moonshine-queue-dashboard::messages.error_deleting_failed_jobs', ToastType::ERROR);
        }
    }
}
