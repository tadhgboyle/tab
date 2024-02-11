<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        $order = $infolist->getRecord();
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make()
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('total_price')
                            ->label('Total Price')
                            ->money(),
                        \Filament\Infolists\Components\TextEntry::make('purchaser_amount')
                            ->label('Purchaser Amount')
                            ->money(),
                        \Filament\Infolists\Components\TextEntry::make('gift_card_amount')
                            ->label('Gift Card Amount')
                            ->money(),
                        \Filament\Infolists\Components\TextEntry::make('purchaser.name')
                            ->label('Purchaser'),
                        \Filament\Infolists\Components\TextEntry::make('cashier.name')
                            ->label('Cashier'),
                        \Filament\Infolists\Components\TextEntry::make('created_at')
                            ->label('Created At'),
                    ]),
            ]);
    }

}
