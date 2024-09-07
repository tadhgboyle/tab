<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Filament\Tables\Table;
use App\Helpers\Permission;
use Filament\Tables\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class ProductsList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->with('variants')
            )
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('category.name')->searchable()->badge()->color('gray'),
                TextColumn::make('price')->state(function (Product $product) {
                    return $product->hasVariants() ? $product->getVariantPriceRange() : $product->price;
                })->sortable(),
                TextColumn::make('stock')->state(function (Product $product) {
                    return $product->getStock();
                })->html()->sortable(),
                BooleanColumn::make('has_variants')->label('Has Variants')->state(function (Product $product) {
                    return $product->hasVariants();
                }),
            ])
            ->filters([
                SelectFilter::make('Category')
                    ->multiple()
                    ->relationship('category', 'name')
                    ->preload(),
                TernaryFilter::make('has_variants')
                    ->label('Has Variants')
                    ->queries(
                        true: fn ($query) => $query->whereHas('variants'),
                        false: fn ($query) => $query->whereDoesntHave('variants'),
                    ),
            ])
            ->actions([
                Action::make('view')
                    ->url(fn (Product $product): string => route('products_view', $product))
                    ->visible(hasPermission(Permission::PRODUCTS_VIEW)),
                Action::make('edit')
                    ->url(fn (Product $product): string => route('products_edit', $product))
                    ->visible(hasPermission(Permission::PRODUCTS_MANAGE)),
            ])
            ->bulkActions([
                // ...
            ])
            ->defaultSort('name');
    }
}
