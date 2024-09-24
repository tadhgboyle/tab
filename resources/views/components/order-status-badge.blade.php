<x-filament::badge :color="$order->status->getColor()">
    {{ $order->status->getLabel() }}
</x-filament::badge>
