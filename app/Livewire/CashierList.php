<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Filament\Tables\Table;
use App\Helpers\Permission;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class CashierList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()->unless(
                    hasPermission(Permission::CASHIER_USERS_OTHER_ROTATIONS),
                    function (Builder $query) {
                        $query->whereHas('rotations', function (Builder $query) {
                            $query->whereIn('id', auth()->user()->rotations->pluck('id'));
                        });
                    }
                )
            )
            ->columns([
                TextColumn::make('full_name')->label('Name')->sortable()->searchable(),
                TextColumn::make('balance')->sortable(),
                TextColumn::make('rotations.name')->badge()->color('gray'),
            ])
            ->filters([
                SelectFilter::make('rotations_id')
                    ->multiple()
                    ->relationship('rotations', 'name')
                    ->preload()
                    ->label('Rotations')
                    ->visible(hasPermission(Permission::CASHIER_USERS_OTHER_ROTATIONS)),
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ])
            ->defaultSort('full_name')
            ->recordUrl(
                fn (User $user) => route('orders_create', $user),
            );
    }
}
