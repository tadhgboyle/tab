<?php

namespace App\Filament\Resources\ActivityRegistrationRelationManagerResource\RelationManagers;

use App\Helpers\TaxHelper;
use App\Models\TransactionProduct;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name'),
                TextColumn::make('price')
                    ->money()
                    ->sortable(),
                TextColumn::make('quantity'),
                TextColumn::make('product_subtotal')
                    ->default(fn (TransactionProduct $product) => TaxHelper::forTransactionProduct(
                        $product, $product->quantity
                    ))
                    ->sortable()
                // IconColumn::make('pst')
                //     ->label('PST')
                //     ->boolean(),
                // IconColumn::make('gst')
                //     ->label('GST')
                //     ->boolean(),
                // TextColumn::make('category.name'),
            ])
            ->filters([
                //
            ]);
    }
}
