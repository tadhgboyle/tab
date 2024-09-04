<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use App\Models\Rotation;
use Filament\Tables\Table;
use App\Enums\RotationStatus;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class RotationsList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public User $user;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Rotations')
            ->query($this->user->rotations()->getQuery())
            ->columns([
                TextColumn::make('name')->badge()->color('gray'),
                TextColumn::make('start')->dateTime('M jS Y h:ia')->sortable(),
                TextColumn::make('end')->dateTime('M jS Y h:ia')->sortable(),
                TextColumn::make('status')->badge()->state(function (Rotation $rotation) {
                    return match ($rotation->getStatus()) {
                        RotationStatus::Present => 'Present',
                        RotationStatus::Future => 'Future',
                        RotationStatus::Past => 'Past',
                    };
                })->color(function (Rotation $rotation) {
                    return match ($rotation->getStatus()) {
                        RotationStatus::Present => 'success',
                        default => 'gray',
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
            ->defaultSort('start')
            ->paginated(false);
    }
}
