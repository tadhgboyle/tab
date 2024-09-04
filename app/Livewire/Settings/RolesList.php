<?php

namespace App\Livewire\Settings;

use App\Models\Role;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class RolesList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Roles')
            ->query(Role::query())
            ->headerActions([
                Action::make('create')
                    ->url(route('settings_roles_create')),
            ])
            ->columns([
                TextColumn::make('name'),
                BooleanColumn::make('staff'),
                TextColumn::make('users_count')->counts('users')->label('Users')->numeric()->sortable(),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                Action::make('edit')
                    ->url(fn (Role $role) => route('settings_roles_edit', $role)),
            ])
            ->bulkActions([
                // ...
            ])
            ->reorderable('order')
            // ->authorizeReorder(fn (Role $role) => !$role->superuser)
            ->defaultSort('order')
            ->paginated(false);
    }
}
