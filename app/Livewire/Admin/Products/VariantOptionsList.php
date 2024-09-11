<?php

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use Livewire\Component;
use Filament\Tables\Table;
use App\Helpers\Permission;
use Filament\Tables\Actions\Action;
use App\Models\ProductVariantOption;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

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
