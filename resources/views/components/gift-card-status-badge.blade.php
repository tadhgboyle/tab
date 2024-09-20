@php
    $color = match(true) {
        $giftCard->expired() => 'danger',
        default => 'gray',
    };
    $status = match(true) {
        $giftCard->expired() => 'Expired',
        default => 'Active',
    };
@endphp
<x-filament::badge :color="$color">
    {{ $status }}
</x-filament::badge>
