<?php

namespace DissNik\MoonShineQueueDashboard\Providers;

use DissNik\MoonShineQueueDashboard\Resources\FailedJobResource;
use Illuminate\Support\ServiceProvider;
use MoonShine\AssetManager\Css;
use MoonShine\AssetManager\Js;
use MoonShine\Contracts\AssetManager\AssetManagerContract;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Contracts\MenuManager\MenuManagerContract;
use MoonShine\MenuManager\MenuItem;
use DissNik\MoonShineQueueDashboard\Pages\QueueDashboardPage;

class MoonShineQueueDashboardServiceProvider extends ServiceProvider
{
    public function boot(CoreContract $core, MenuManagerContract $menu, AssetManagerContract $assets): void
    {
        $this->loadViewsFrom(
            __DIR__ . '/../../resources/views',
            'moonshine-queue-dashboard'
        );

        $this->loadTranslationsFrom(
            __DIR__ . '/../../resources/lang',
            'moonshine-queue-dashboard'
        );

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/queue-dashboard.php',
            'moonshine-queue-dashboard'
        );

        $this->publishes(
            [__DIR__ . '/../../config/queue-dashboard.php' => config_path('moonshine-queue-dashboard.php')],
            ['moonshine-queue-dashboard', 'moonshine-queue-dashboard-config', 'laravel-config']
        );

        $this->publishes(
            [__DIR__ . '/../../resources/lang' => $this->app->langPath('vendor/moonshine-queue-dashboard')],
            ['moonshine-queue-dashboard', 'moonshine-queue-dashboard-lang', 'laravel-lang']
        );


        $this->publishes(
            [__DIR__ . '/../../public' => public_path('vendor/moonshine-queue-dashboard')],
            ['moonshine-queue-dashboard', 'moonshine-queue-dashboard-assets', 'laravel-assets']
        );

        $core->pages([
                QueueDashboardPage::class
            ])
            ->resources([
                FailedJobResource::class
            ]);

        if (config('moonshine-queue-dashboard.auto_menu', true)) {
            $menu->add([
                MenuItem::make(__('moonshine-queue-dashboard::dashboard.queue_dashboard'), QueueDashboardPage::class)
            ]);
        }

        $assets->add([
            Css::make('vendor/moonshine-queue-dashboard/stylesheet.css'),
            Js::make('vendor/moonshine-queue-dashboard/script.js'),
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/queue-dashboard.php', 'queue_dashboard'
        );
    }
}
