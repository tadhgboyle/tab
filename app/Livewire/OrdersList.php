<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;
use App\Enums\OrderStatus;
use Filament\Tables\Table;
use App\Helpers\Permission;
use Filament\Tables\Actions\Action;
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
                TextColumn::make('created_at')->label('Time')->dateTime('M jS Y h:ia')->sortable(),
                TextColumn::make('purchaser.full_name')->searchable()->sortable()
                    ->url(fn (Order $order) => route('users_view', $order->purchaser)),
                TextColumn::make('cashier.full_name')->searchable()->sortable()
                    ->url(fn (Order $order) => route('users_view', $order->cashier)),
                TextColumn::make('total_price')->sortable(),
                TextColumn::make('status')->badge()->state(function (Order $order) {
                    return $order->status->getWord();
                })->color(function (Order $order) {
                    return match ($order->status) {
                        OrderStatus::NotReturned => 'gray',
                        OrderStatus::PartiallyReturned => 'primary',
                        OrderStatus::FullyReturned => 'danger',
                    };
                }),
            ])
            ->filters([
                SelectFilter::make('Status')->options([
                    OrderStatus::NotReturned->value => OrderStatus::NotReturned->getWord(),
                    OrderStatus::PartiallyReturned->value => OrderStatus::PartiallyReturned->getWord(),
                    OrderStatus::FullyReturned->value => OrderStatus::FullyReturned->getWord(),
                ])->multiple(),
            ])
            ->actions([
                Action::make('view')
                    ->url(fn (Order $order) => route('orders_view', $order))
                    ->visible(hasPermission(Permission::ORDERS_VIEW)),
            ])
            ->bulkActions([
                // ...
            ])
            ->defaultSort('created_at', 'desc');
    }
}
