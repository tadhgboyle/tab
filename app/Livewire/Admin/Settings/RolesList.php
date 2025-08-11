<?php

namespace App\Livewire\Admin\Settings;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use App\Models\Role;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class RolesList extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
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
            ->recordActions([
                Action::make('edit')
                    ->url(fn (Role $role) => route('settings_roles_edit', $role)),
            ])
            ->toolbarActions([
                // ...
            ])
            ->reorderable('order')
            // ->authorizeReorder(fn (Role $role) => !$role->superuser)
            ->defaultSort('order')
            ->paginated(false);
    }
}
