<?php

namespace App\Livewire\Admin\Products\VariantOptions;

use Livewire\Component;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use App\Models\ProductVariantOption;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use App\Models\ProductVariantOptionValue;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class ValuesList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public ProductVariantOption $productVariantOption;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->productVariantOption->values()->getQuery())
            ->headerActions([
                Action::make('create')
                    ->alpineClickHandler('openCreateValueModal')
            ])
            ->columns([
                TextColumn::make('value'),
                TextColumn::make('variants_count')->counts('variants')->label('Variants')->sortable(),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                Action::make('edit')
                    ->alpineClickHandler(function (ProductVariantOptionValue $productVariantOptionValue) {
                        return "openEditValueModal('{$productVariantOptionValue->id}', '{$productVariantOptionValue->value}')";
                    }),
            ])
            ->bulkActions([
                // ...
            ])
            ->paginated(false);
    }
}
