<?php

namespace App\Livewire\Products;

use App\Helpers\Permission;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class VariantsList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public Product $product;

    public function table(Table $table): Table
    {
        $columns = [
            TextColumn::make('sku')->label('SKU')
                ->fontFamily(FontFamily::Mono)
                ->searchable(),
        ];

        foreach ($this->product->variantOptions as $variantOption) {
            $columns[] = TextColumn::make($variantOption->name)
                ->state(fn (ProductVariant $productVariant) => $productVariant->optionValueFor($variantOption)?->value)
                ->badge()->color('gray');
        }

        $columns[] = TextColumn::make('stock')->sortable()->numeric();
        $columns[] = TextColumn::make('box_size')->sortable()->numeric();
        $columns[] = TextColumn::make('price')->sortable();

        return $table
            ->heading('Variants')
            ->query($this->product->variants()->getQuery())
            ->headerActions([
                Action::make('create')
                    ->url(route('products_variants_create', $this->product))
                    ->visible(hasPermission(Permission::PRODUCTS_MANAGE) && !$this->product->variantOptions->isEmpty() && !$this->product->hasAllVariantCombinations()),
            ])
            ->columns($columns)
            ->filters([
                // ...
            ])
            ->actions([
                Action::make('edit')
                    ->url(fn (ProductVariant $productVariant) => route('products_variants_edit', [$this->product, $productVariant]))
                    ->visible(hasPermission(Permission::PRODUCTS_MANAGE)),
            ])
            ->bulkActions([
                // ...
            ])
            ->paginated(false);
    }
}