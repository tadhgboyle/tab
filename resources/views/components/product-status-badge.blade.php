<x-filament::badge :color="$product->status->getColor()">
    {{ $product->status->getLabel() }}
</x-filament::badge>
