<?php

namespace App\Livewire\Admin\Products;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductInventoryAdjustment;
use App\Models\User;
use Livewire\Component;
use Filament\Tables\Table;
use App\Helpers\Permission;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class InventoryAdjustmentsList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public Product $product;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Inventory Adjustments')
            ->query($this->product->inventoryAdjustments()->with('causer')->getQuery())
            ->columns([
                TextColumn::make('created_at')->sortable()->dateTime('M jS Y h:ia'),
                TextColumn::make('reason'),
                TextColumn::make('adjustment')->sortable(),
                TextColumn::make('new_quantity'),
                TextColumn::make('causer')->state(function (ProductInventoryAdjustment $inventoryAdjustment) {
                    $causer = $inventoryAdjustment->causer;

                    if ($causer instanceof User) {
                        return $causer->full_name;
                    }

                    if ($causer instanceof Order) {
                        return "Order $causer->identifier";
                    }

                    // if ($causer instanceof PurchaseOrder) {
                    //     return "Purchase Order: $causer->identifier";
                    // }
                })->url(function (ProductInventoryAdjustment $inventoryAdjustment) {
                    $causer = $inventoryAdjustment->causer;

                    if ($causer instanceof User && hasPermission(Permission::USERS_VIEW)) {
                        return route('users_view', $causer);
                    }

                    if ($causer instanceof Order && hasPermission(Permission::ORDERS_VIEW)) {
                        return route('orders_view', $causer);
                    }

                    // if ($causer instanceof PurchaseOrder) {
                    //     return route('admin.purchase-orders.edit', $causer);
                    // }
                }),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ])
            ->paginated(false)
            ->defaultSort('created_at', 'desc');
    }
}
