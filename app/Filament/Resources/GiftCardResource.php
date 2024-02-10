<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GiftCardResource\Pages;
use App\Filament\Resources\GiftCardResource\RelationManagers;
use App\Models\GiftCard;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\FontFamily;

class GiftCardResource extends Resource
{
    protected static ?string $model = GiftCard::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('code')
                    ->hiddenOn('edit')
                    ->required()
                    ->maxLength(255),
                TextInput::make('original_balance')
                    ->prefix('$')
                    ->name('Balance')
                    ->required()
                    ->hiddenOn('edit')
                    ->numeric(),
                TextInput::make('remaining_balance')
                    ->prefix('$')
                    ->name('Remaining Balance')
                    ->required()
                    ->hiddenOn('create')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->fontFamily(FontFamily::Mono)
                    ->copyable()
                    ->copyMessage('Gift card code copied')
                    ->copyMessageDuration(1500)
                    ->searchable(),
                TextColumn::make('issuer.name'),
                TextColumn::make('original_balance')
                    ->money()
                    ->sortable(),
                TextColumn::make('remaining_balance')
                    ->money()
                    ->sortable(),
                TextColumn::make('uses_count')
                    ->counts('uses')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageGiftCards::route('/'),
        ];
    }
}
