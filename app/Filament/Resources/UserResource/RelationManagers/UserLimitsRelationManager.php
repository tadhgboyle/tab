<?php

namespace App\Filament\Resources\UserLimitsRelationManagerResource\RelationManagers;

use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\UserLimit;

class UserLimitsRelationManager extends RelationManager
{
    protected static string $relationship = 'userLimits';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->hiddenOn('edit')
                    ->name('Category')
                    ->options(Category::query()
                        ->whereNotIn('id', $this->getOwnerRecord()->userLimits->map->category_id)
                        ->pluck('name', 'id')
                        ->toArray()
                    )
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('limit')
                    ->prefix('$')
                    ->numeric(),
                Forms\Components\Select::make('duration')
                    ->options([
                        UserLimit::LIMIT_DAILY => 'Day',
                        UserLimit::LIMIT_WEEKLY => 'Week',
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category.name'),
                Tables\Columns\TextColumn::make('limit')->money(),
                Tables\Columns\ViewColumn::make('spent')->view('filament.tables.columns.user-limit-spent'),
                Tables\Columns\ViewColumn::make('remaining')->view('filament.tables.columns.user-limit-remaining'),
                TextColumn::make('duration')->badge()->color('gray')->formatStateUsing(fn (int $state): string => match ($state) {
                    UserLimit::LIMIT_DAILY => 'Day',
                    UserLimit::LIMIT_WEEKLY => 'Week',
                }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->hidden(function () {
                    return $this->getOwnerRecord()
                        ->userLimits()
                        ->count() === Category::count();
                })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
