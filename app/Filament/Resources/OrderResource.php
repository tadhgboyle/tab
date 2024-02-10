<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Rotation;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;

class OrderResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $modelLabel = 'Order';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('rotation.name'),
                TextColumn::make('purchaser.name')
                    ->searchable()
                    ->color('primary')
                    ->url(fn (Transaction $transaction): ?string => UserResource::getUrl('view', ['record' => $transaction->purchaser])),
                TextColumn::make('cashier.name')
                    ->color('primary')
                    ->url(fn (Transaction $transaction): ?string => UserResource::getUrl('view', ['record' => $transaction->cashier])),
                TextColumn::make('total_price')
                    ->money()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money(),
                    ]),
                TextColumn::make('status')->badge()->color(fn (int $state): string => match ($state) {
                    Transaction::STATUS_NOT_RETURNED => 'success',
                    Transaction::STATUS_PARTIAL_RETURNED => 'warning',
                    Transaction::STATUS_FULLY_RETURNED => 'danger',
                })->formatStateUsing(fn (int $state): string => match ($state) {
                    Transaction::STATUS_NOT_RETURNED => 'Not Returned',
                    Transaction::STATUS_PARTIAL_RETURNED => 'Partially Returned',
                    Transaction::STATUS_FULLY_RETURNED => 'Returned',
                })->icon(fn (int $state): string => match ($state) {
                    Transaction::STATUS_NOT_RETURNED => 'heroicon-o-check-circle',
                    Transaction::STATUS_PARTIAL_RETURNED => 'heroicon-o-exclamation-triangle',
                    Transaction::STATUS_FULLY_RETURNED => 'heroicon-o-x-circle',
                }),
                TextColumn::make('products_count')
                    ->counts('products')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        Transaction::STATUS_NOT_RETURNED => 'Not Returned',
                        Transaction::STATUS_FULLY_RETURNED => 'Returned',
                        Transaction::STATUS_PARTIAL_RETURNED => 'Partially Returned',
                    ]),
                Tables\Filters\SelectFilter::make('rotation_id')
                    ->name('Rotation')
                    ->options(Rotation::pluck('name', 'id')->toArray()),
            ])
            ->groups([
                Tables\Grouping\Group::make('created_at')
                    ->label('Order Date')
                    ->date()
                    ->collapsible(),
                Tables\Grouping\Group::make('rotation_id')
                    ->label('Rotation')
                    ->getTitleFromRecordUsing(fn (Transaction $transaction): string => $transaction->rotation->name)
                    ->collapsible(),
            ])
            ->actions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
        ];
    }
}
