@props([
    'error' => '',
    'stack' => [],
])

<div {{ $attributes }}>
    <x-moonshine::layout.box class="bg-body max-h-[500px] overflow-auto" :dark="true">
        <x-moonshine::badge color="error">{{ $error }}</x-moonshine::badge>
        <x-moonshine::layout.div class="text-md">{{ __('moonshine-queue-dashboard::dashboard.stack_trace') }}:</x-moonshine::layout.div>
        <ol class="font-mono">
            @foreach($stack as $i => $line)
                <li>
                    {!! trim($line) !!}
                </li>
            @endforeach
        </ol>
    </x-moonshine::layout.header>
</div>
