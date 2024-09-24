<?php

namespace App\Livewire\Admin\Settings;

use Livewire\Component;
use App\Models\Rotation;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class RotationsList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Rotations')
            ->query(Rotation::query())
            ->headerActions([
                Action::make('create')
                    ->url(route('settings_rotations_create')),
            ])
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('users_count')->counts('users')->label('Users')->numeric()->sortable(),
                TextColumn::make('start')->dateTime('M jS Y h:ia')->sortable(),
                TextColumn::make('end')->dateTime('M jS Y h:ia')->sortable(),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                Action::make('edit')
                    ->url(fn (Rotation $rotation) => route('settings_rotations_edit', $rotation)),
            ])
            ->bulkActions([
                // ...
            ])
            ->defaultSort('start')
            ->paginated(false);
    }
}
