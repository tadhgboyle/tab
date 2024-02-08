<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Widgets\OrderOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Transaction;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            OrderOverview::class,
        ];
    }

    // public function getTabs(): array
    // {
    //     return [
    //         'all' => Tab::make('All orders'),
    //         'returned' => Tab::make('Returned orders')
    //             ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Transaction::STATUS_FULLY_RETURNED)),
    //         'semi-returned' => Tab::make('Semi returned orders')
    //             ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Transaction::STATUS_PARTIAL_RETURNED)),
    //     ];
    // }

}
