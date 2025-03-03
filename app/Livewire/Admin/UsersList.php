<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Filament\Tables\Table;
use App\Helpers\Permission;
use App\Helpers\RotationHelper;
use Filament\Tables\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

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
                            $query->where('id', app(RotationHelper::class)->getCurrentRotation()->id);
                        });
                    }
                )
            )
            ->heading('Users')
            ->headerActions(
                hasPermission(Permission::USERS_MANAGE) ? [
                    Action::make('create')->url(route('users_create'))
                ] : []
            )
            ->columns([
                TextColumn::make('full_name')->label('Name')->sortable()->searchable(),
                TextColumn::make('username')->sortable()->searchable(),
                TextColumn::make('family.name')->badge()->color('gray')->searchable(),
                TextColumn::make('balance')->sortable(),
                TextColumn::make('role.name')->badge()->color('gray'),
                TextColumn::make('rotations.name')->badge()->color('gray'),
            ])
            ->recordUrl(function (User $record) {
                if (hasPermission(Permission::USERS_VIEW)) {
                    return route('users_view', $record);
                }
            })
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
                TernaryFilter::make('in_family')
                    ->label('In Family')
                    ->queries(
                        true: fn ($query) => $query->whereHas('family'),
                        false: fn ($query) => $query->whereDoesntHave('family'),
                    ),
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ])
            ->defaultSort('full_name');
    }
}
