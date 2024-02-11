<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RotationResource\Pages;
use App\Filament\Resources\RotationResource\RelationManagers;
use App\Helpers\RotationHelper;
use App\Models\Rotation;
use App\Rules\RotationDoesNotOverlap;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RotationResource extends Resource
{
    protected static ?string $model = Rotation::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        $record = $form->getRecord();

        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\DateTimePicker::make('start')
                    ->before('end')
                    ->rule(self::doesNotOverlapRule($record?->id))
                    ->required(),
                Forms\Components\DateTimePicker::make('end')
                    ->after('start')
                    ->rule(self::doesNotOverlapRule($record?->id))
                    ->required(),
            ]);
    }

    private static function doesNotOverlapRule(?int $ignoreId): Closure
    {
        return fn () => function (string $attribute, $value, Closure $fail) use ($ignoreId) {
            if (app(RotationHelper::class)->doesRotationOverlap(request()->input('start'), request()->input('end'), $ignoreId)) {
                $fail('That would overlap with another rotation.');
            }
        };
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->sortable(),
                Tables\Columns\ViewColumn::make('status')->view('filament.tables.columns.rotation-status'),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRotations::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
