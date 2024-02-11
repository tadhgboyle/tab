<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\Payout;
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
use Filament\Support\Enums\FontFamily;

class PayoutsRelationManager extends RelationManager
{
    protected static string $relationship = 'payouts';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('identifier')
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->prefix('$')
                    ->required()
                    ->numeric(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('identifier')
                    ->fontFamily(FontFamily::Mono)
                    ->searchable(),
                Tables\Columns\TextColumn::make('cashier.name')
                    ->color('primary')
                    ->url(fn (Payout $payout): ?string => UserResource::getUrl('view', ['record' => $payout->cashier])),
                Tables\Columns\TextColumn::make('amount')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->mutateFormDataUsing(function (array $data): array {
                    $data['cashier_id'] = auth()->id();
             
                    return $data;
                })
            ])
            ->filters([
                //
            ]);
    }
}
