<x-moonshine::layout.grid :gap="4">
    @foreach($generalMetrics as $metric)
        {{ $metric->render() }}
    @endforeach
</x-moonshine::layout.grid>

<x-moonshine::layout.line-break />

@if(count($queueMetrics))
    <x-moonshine::layout.grid :gap="4">
        @foreach($queueMetrics as $metric)
            <x-moonshine::layout.column adaptiveColSpan="6" colSpan="4">
                {{ $metric->render() }}
            </x-moonshine::layout.column>
        @endforeach
    </x-moonshine::layout.grid>
@endif
