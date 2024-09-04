<?php

namespace App\Livewire;

use App\Helpers\Permission;
use App\Models\User;
use Filament\Tables\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class UsersList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()->unless(
                    hasPermission(Permission::USERS_LIST_SELECT_ROTATION),
                    function (Builder $query) {
                        $query->whereHas('rotations', function (Builder $query) {
                            $query->whereIn('id', auth()->user()->rotations->pluck('id'));
                        });
                    }
                )
            )
            ->columns([
                TextColumn::make('full_name')->label('Name')->sortable()->searchable(),
                TextColumn::make('username')->sortable()->searchable(),
                TextColumn::make('balance')->sortable(),
                TextColumn::make('role.name')->badge()->color('gray'),
                TextColumn::make('rotations.name')->badge()->color('gray'),
            ])
            ->filters([
                SelectFilter::make('Role')
                    ->multiple()
                    ->relationship('role', 'name')
                    ->preload(),
                SelectFilter::make('rotations_id')
                    ->multiple()
                    ->relationship('rotations', 'name')
                    ->preload()
                    ->label('Rotations')
                    ->visible(hasPermission(Permission::USERS_LIST_SELECT_ROTATION)),
            ])
            ->actions([
                Action::make('view')
                    ->url(fn (User $record): string => route('users_view', $record))
                    ->visible(hasPermission(Permission::USERS_VIEW)),
                Action::make('edit')
                    ->url(fn (User $record): string => route('users_edit', $record))
                    ->visible(hasPermission(Permission::USERS_MANAGE)),
            ])
            ->bulkActions([
                // ...
            ])
            ->defaultSort('full_name');
    }
}
