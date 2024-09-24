<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use Livewire\Component;
use App\Enums\OrderStatus;
use Filament\Tables\Table;
use App\Helpers\Permission;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class OrdersList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query())
            ->columns([
                TextColumn::make('identifier')->searchable()->sortable(),
                TextColumn::make('created_at')->label('Time')->dateTime('M jS Y h:ia')->sortable(),
                TextColumn::make('purchaser.full_name')->searchable()->sortable()
                    ->url(fn (Order $order) => route('users_view', $order->purchaser)),
                TextColumn::make('cashier.full_name')->searchable()->sortable()
                    ->url(fn (Order $order) => route('users_view', $order->cashier)),
                TextColumn::make('total_price')->sortable(),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                SelectFilter::make('status')->options(OrderStatus::class)->multiple(),
            ])
            ->recordUrl(function (Order $order) {
                if (hasPermission(Permission::ORDERS_VIEW)) {
                    return route('orders_view', $order);
                }
            })
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ])
            ->defaultSort('created_at', 'desc');
    }
}
