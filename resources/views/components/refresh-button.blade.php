@props([
    'autoRefresh' => false,
    'refreshInterval' => 10000,
])

<button {{ $attributes->class(['btn btn-square'])->merge(['type' => 'button']) }}
        x-data="autoRefresh({
            autoRefresh: @js($autoRefresh),
            refreshInterval: @js($refreshInterval)
        })"
        @click="toggleAutoRefresh"
>
    <x-moonshine::icon
        icon="play"
        x-show="!autoRefresh"
        style="display: none;"
    />
    <x-moonshine::icon
        icon="pause"
        x-show="autoRefresh"
        style="display: none;"
    />
</button>
