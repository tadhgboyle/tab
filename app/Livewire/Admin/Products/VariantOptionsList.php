<?php

namespace App\Livewire\Admin\Products;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use App\Models\Product;
use Livewire\Component;
use Filament\Tables\Table;
use App\Helpers\Permission;
use App\Models\ProductVariantOption;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class VariantOptionsList extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
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
            ->recordActions([
                Action::make('edit')
                    ->url(fn (ProductVariantOption $variantOption) => route('products_variant-options_edit', [$this->product, $variantOption]))
                    ->visible(hasPermission(Permission::PRODUCTS_MANAGE)),
            ])
            ->toolbarActions([
                // ...
            ])
            ->paginated(false);
    }
}
