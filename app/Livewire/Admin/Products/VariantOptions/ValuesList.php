<?php

namespace App\Livewire\Admin\Products\VariantOptions;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use Livewire\Component;
use Filament\Tables\Table;
use App\Models\ProductVariantOption;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use App\Models\ProductVariantOptionValue;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class ValuesList extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
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
            ->recordActions([
                Action::make('edit')
                    ->alpineClickHandler(function (ProductVariantOptionValue $productVariantOptionValue) {
                        return "openEditValueModal('{$productVariantOptionValue->id}', '{$productVariantOptionValue->value}')";
                    }),
            ])
            ->toolbarActions([
                // ...
            ])
            ->paginated(false);
    }
}
