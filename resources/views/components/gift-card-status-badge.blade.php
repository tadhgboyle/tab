<x-filament::badge :color="$giftCard->status->getColor()">
    {{ $giftCard->status->getLabel() }}
</x-filament::badge>
