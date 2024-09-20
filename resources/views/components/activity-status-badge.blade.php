@php
    $color = match(true) {
        $activity->ended() => 'danger',
        $activity->inProgress() => 'success',
        default => 'gray',
    };
    $status = match(true) {
        $activity->ended() => 'Ended',
        $activity->inProgress() => 'In Progress',
        default => 'Upcoming',
    };
@endphp
<x-filament::badge :color="$color">
    {{ $status }}
</x-filament::badge>
