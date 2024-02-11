<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\Rotation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use App\Models\Transaction;
use App\Filament\Resources\UserResource;

class RotationsRelationManager extends RelationManager
{
    protected static string $relationship = 'rotations';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        $user_rotation_count = $this->getOwnerRecord()->rotations->count();
        $rotation_count = Rotation::count();

        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('start')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\ViewColumn::make('status')->view('filament.tables.columns.rotation-status'),
            ])->actions([
                DetachAction::make()
            ])->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->hidden(fn() => $user_rotation_count === $rotation_count)
                    //->attachAnother(fn() => !($rotation_count - 1 === $user_rotation_count)),
            ]);
    }
}
