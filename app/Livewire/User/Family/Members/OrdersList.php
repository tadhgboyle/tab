<?php

namespace App\Livewire\User\Family\Members;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class OrdersList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public User $user;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Orders')
            ->query($this->user->orders()->getQuery())
            ->columns([
                TextColumn::make('identifier'),
                TextColumn::make('created_at')->label('Time')->dateTime()->sortable(),
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
                // ...
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ])
            ->paginated(false);
    }
}
