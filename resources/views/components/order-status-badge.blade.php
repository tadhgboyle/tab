@php
    $color = match($order->status) {
        \App\Enums\OrderStatus::FullyReturned => 'danger',
        \App\Enums\OrderStatus::PartiallyReturned => 'warning',
        default => 'gray',
    };
    $status = match($order->status) {
        \App\Enums\OrderStatus::FullyReturned => 'Fully Returned',
        \App\Enums\OrderStatus::PartiallyReturned => 'Partially Returned',
        default => 'Not Returned',
    };
@endphp
<x-filament::badge :color="$color">
    {{ $status }}
</x-filament::badge>
