<?php

namespace App\Livewire\Admin;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderProduct;
use Cknow\Money\Money;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Filament\Tables\Table;
use App\Helpers\Permission;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class PurchaseOrderProductsList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public PurchaseOrder $purchaseOrder;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->purchaseOrder->products()->getQuery())
            ->heading('Products')
            ->columns([
                TextColumn::make('product.name')->sortable()->searchable()->url(function (PurchaseOrderProduct $purchaseOrderProduct) {
                    if (hasPermission(Permission::PRODUCTS_VIEW)) {
                        return route('products_view', $purchaseOrderProduct->product);
                    }
                }),
                TextColumn::make('quantity')->numeric()->sortable(),
                TextColumn::make('received_quantity')->numeric()->sortable(),
                TextColumn::make('cost')->numeric()->sortable(),
                TextColumn::make('total_cost')->numeric()->state(function (PurchaseOrderProduct $purchaseOrderProduct) {
                    return $purchaseOrderProduct->cost->multiply($purchaseOrderProduct->quantity);
                }),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                BulkAction::make('receive')
                    ->requiresConfirmation()
                    ->visible(function () {
                        return $this->purchaseOrder->status === PurchaseOrderStatus::Pending;
                    })
                    ->action(function (Collection $purchaseOrderProducts) {
                        DB::transaction(function () use ($purchaseOrderProducts) {
                            $purchaseOrderProducts->each->receive();
                        });
                    }),
            ])
            ->checkIfRecordIsSelectableUsing(fn (PurchaseOrderProduct $purchaseOrderProduct) => $purchaseOrderProduct->quantity > $purchaseOrderProduct->received_quantity)
            ->paginated(false);
    }
}
