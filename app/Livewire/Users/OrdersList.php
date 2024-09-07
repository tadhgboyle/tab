<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Models\Order;
use Livewire\Component;
use App\Enums\OrderStatus;
use Filament\Tables\Table;
use App\Helpers\Permission;
use Filament\Tables\Actions\Action;
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
            ->headerActions([
                Action::make('create')
                    ->url(route('orders_create', $this->user))
                    ->visible(hasPermission($this->user->id === auth()->id() ? Permission::CASHIER_SELF_PURCHASES : Permission::CASHIER_CREATE)),
            ])
            ->columns([
                TextColumn::make('created_at')->label('Time')->dateTime()->sortable(),
                TextColumn::make('cashier.full_name')
                    ->url(function (Order $order) {
                        if (!hasPermission(Permission::USERS_VIEW)) {
                            return null;
                        }

                        return route('users_view', $order->cashier);
                    }),
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
                Action::make('view')
                    ->url(fn (Order $order)=> route('orders_view', $order))
                    ->visible(hasPermission(Permission::ORDERS_VIEW)),
            ])
            ->bulkActions([
                // ...
            ])
            ->paginated(false);
    }
}
