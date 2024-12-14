<?php

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use Livewire\Component;
use Filament\Tables\Table;
use App\Helpers\Permission;
use App\Models\ProductVariant;
use Filament\Tables\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

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
        $columns[] = TextColumn::make('cost')->sortable();

        return $table
            ->heading('Variants')
            ->query(
                $this->product->variants()->with(
                    'optionValueAssignments',
                    'optionValueAssignments.productVariantOption',
                    'optionValueAssignments.productVariantOptionValue'
                )->getQuery()
            )
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
