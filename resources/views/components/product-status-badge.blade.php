@php
    $color = match(true) {
        $product->isActive() => 'success',
        default => 'gray',
    };
    $status = match(true) {
        $product->isActive() => 'Active',
        default => 'Draft',
    };
@endphp
<x-filament::badge :color="$color">
    {{ $status }}
</x-filament::badge>
