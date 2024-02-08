<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use App\Models\Transaction;
use App\Filament\Resources\UserResource;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';
    protected static ?string $title = 'Orders';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('cashier.name')
                    ->color('primary')
                    ->url(fn (Transaction $transaction): ?string => UserResource::getUrl('view', ['record' => $transaction->cashier])),
                TextColumn::make('total_price')
                    ->numeric()
                    ->sortable(),
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
            ]);
    }
}
