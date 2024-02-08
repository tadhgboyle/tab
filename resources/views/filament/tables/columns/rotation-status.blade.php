@switch($getRecord()->getStatus())
        @case(\App\Models\Rotation::STATUS_PRESENT)
            @php
                $word = "Present";
                $icon = "heroicon-o-arrow-path";
            @endphp
            @break
        @case(\App\Models\Rotation::STATUS_FUTURE)
            @php
                $word = "Future";
                $icon = "heroicon-o-clock";
            @endphp
            @break
        @case(\App\Models\Rotation::STATUS_PAST)
            @php
                $word = "Past";
                $icon = "heroicon-o-check-circle";
            @endphp
            @break
    @endswitch
<x-filament::badge color="gray" icon="{{ $icon }}">
    {{ $word }}
</x-filament::badge>