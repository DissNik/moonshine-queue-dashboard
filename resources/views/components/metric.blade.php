@props([
    'metric'
])

<x-moonshine::layout.box :title="__('moonshine-queue-dashboard::dashboard.queue_name', ['name' => $metric->queue])" class="zoom-in h-full">
    <x-moonshine::layout.div>
        <x-moonshine::layout.div>
            {{ __('moonshine-queue-dashboard::dashboard.status') }}:
            <x-moonshine::badge :color="$metric->status == 'OK' ? 'success' : 'error'" class=" font-bold">
                {{ $metric->status }}
            </x-moonshine::badge>
        </x-moonshine::layout.div>
        <x-moonshine::layout.div>
            {{ __('moonshine-queue-dashboard::dashboard.connection') }}:
            <x-moonshine::badge color="gray">
                {{ $metric->connection }}
            </x-moonshine::badge>
        </x-moonshine::layout.div>
    </x-moonshine::layout.div>

    <x-moonshine::layout.divider :isCentered="true" :label="__('jobs')" class="text-3xs"></x-moonshine::layout.divider>

    <x-moonshine::layout.grid :gap="0" class="divide-x divide-slate-200 dark:divide-dark-100">
        <x-moonshine::layout.column adaptiveColSpan="3" colSpan="3">
            <x-moonshine::layout.div class="px-0.5">
                <x-moonshine::layout.div class="text-center text-md">{{ $metric->pending }}</x-moonshine::layout.div>
                <x-moonshine::layout.div class="text-center text-3xs text-dark-400 dark:text-slate-400">
                    <small>{{ __('moonshine-queue-dashboard::dashboard.pending') }}</small>
                </x-moonshine::layout.div>
            </x-moonshine::layout.div>
        </x-moonshine::layout.column>

        <x-moonshine::layout.column adaptiveColSpan="3" colSpan="3">
            <x-moonshine::layout.div class="px-0.5">
                <x-moonshine::layout.div class="text-center text-md">{{ $metric->delayed }}</x-moonshine::layout.div>
                <x-moonshine::layout.div class="text-center text-3xs text-dark-400 dark:text-slate-400">
                    <small>{{ __('moonshine-queue-dashboard::dashboard.delayed') }}</small>
                </x-moonshine::layout.div>
            </x-moonshine::layout.div>
        </x-moonshine::layout.column>

        <x-moonshine::layout.column adaptiveColSpan="3" colSpan="3">
            <x-moonshine::layout.div class="px-0.5">
                <x-moonshine::layout.div class="text-center text-md">{{ $metric->reserved }}</x-moonshine::layout.div>
                <x-moonshine::layout.div class="text-center text-3xs text-dark-400 dark:text-slate-400">
                    <small>{{ __('moonshine-queue-dashboard::dashboard.reserved') }}</small>
                </x-moonshine::layout.div>
            </x-moonshine::layout.div>
        </x-moonshine::layout.column>

        <x-moonshine::layout.column adaptiveColSpan="3" colSpan="3">
            <x-moonshine::layout.div class="px-0.5">
                <x-moonshine::layout.div class="text-center text-md">{{ $metric->size }}</x-moonshine::layout.div>
                <x-moonshine::layout.div class="text-center text-3xs text-dark-400 dark:text-slate-400">
                    <small>{{ __('moonshine-queue-dashboard::dashboard.total') }}</small>
                </x-moonshine::layout.div>
            </x-moonshine::layout.div>
        </x-moonshine::layout.column>
    </x-moonshine::layout.grid>
</x-moonshine::layout.box>
