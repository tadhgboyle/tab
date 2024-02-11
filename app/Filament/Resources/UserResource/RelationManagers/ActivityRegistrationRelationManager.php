<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\ActivityRegistration;
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

class ActivityRegistrationRelationManager extends RelationManager
{
    protected static string $relationship = 'activityRegistrations';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('activity.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cashier.name')
                    ->color('primary')
                    ->url(fn (ActivityRegistration $registration): ?string => UserResource::getUrl('view', ['record' => $registration->cashier])),
                Tables\Columns\TextColumn::make('activity_price')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
            ]);
    }
}
