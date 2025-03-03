<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use Livewire\Component;
use Filament\Tables\Table;
use App\Helpers\Permission;
use App\Enums\ProductStatus;
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
                    ->unless(hasPermission(Permission::PRODUCTS_VIEW_DRAFT), fn ($query) => $query->where('status', ProductStatus::Active))
            )
            ->heading('Products')
            ->headerActions(
                hasPermission(Permission::PRODUCTS_MANAGE) ? [
                    Action::make('create')->url(route('products_create'))
                ] : []
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
                TextColumn::make('status')->badge()->visible(hasPermission(Permission::PRODUCTS_VIEW_DRAFT)),
            ])
            ->recordUrl(function (Product $product) {
                if (hasPermission(Permission::PRODUCTS_VIEW)) {
                    return route('products_view', $product);
                }
            })
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
                SelectFilter::make('status')
                    ->options(ProductStatus::class)->visible(hasPermission(Permission::PRODUCTS_VIEW_DRAFT)),
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ])
            ->defaultSort('name');
    }
}
