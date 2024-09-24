<x-filament::badge :color="$activity->status->getColor()">
    {{ $activity->status->getLabel() }}
</x-filament::badge>
