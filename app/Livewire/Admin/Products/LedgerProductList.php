<?php

namespace App\Livewire\Admin\Products;

use Livewire\Component;
use App\Models\Category;
use Filament\Tables\Table;
use App\Enums\CategoryType;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\BooleanColumn;
use App\Models\Proxies\ProductsVariantsProxy;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class LedgerProductList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function setStockAdjustingProduct($recordKey)
    {
        $this->dispatch('openAdjustModal', [
            'record' => ProductsVariantsProxy::find($recordKey),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(ProductsVariantsProxy::query())
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('sku')->searchable()->fontFamily(FontFamily::Mono)->copyable()->label('SKU'),
                TextColumn::make('category_name')->label('Category')->badge()->color('gray'),
                TextColumn::make('category_id')->hidden(),
                TextColumn::make('stock')->numeric()->sortable(),
                BooleanColumn::make('stock_override'),
                TextColumn::make('box_size')->numeric()->sortable(),
            ])
            ->recordAction(
                'setStockAdjustingProduct',
            )
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->multiple()
                    ->options(Category::query()->whereNot('type', CategoryType::Activities)->pluck('name', 'id')->toArray())
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
