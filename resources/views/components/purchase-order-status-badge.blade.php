<x-filament::badge :color="$purchaseOrder->status->getColor()">
    {{ $purchaseOrder->status->getLabel() }}
</x-filament::badge>
