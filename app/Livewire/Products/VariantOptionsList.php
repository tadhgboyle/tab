<?php

namespace App\Livewire\Products;

use App\Helpers\Permission;
use App\Models\Product;
use App\Models\ProductVariantOption;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class VariantOptionsList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public Product $product;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Options')
            ->query($this->product->variantOptions()->getQuery())
            ->headerActions([
                Action::make('create')
                    ->url(route('products_variant-options_create', $this->product))
                    ->visible(hasPermission(Permission::PRODUCTS_MANAGE)),
            ])
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('values.value')->badge()->color('gray'),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                Action::make('edit')
                    ->url(fn (ProductVariantOption $variantOption) => route('products_variant-options_edit', [$this->product, $variantOption]))
                    ->visible(hasPermission(Permission::PRODUCTS_MANAGE)),
            ])
            ->bulkActions([
                // ...
            ])
            ->paginated(false);
    }
}