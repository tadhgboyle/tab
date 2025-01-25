<?php

namespace App\Livewire\Admin;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderProduct;
use Cknow\Money\Money;
use Filament\Tables\Actions\Action;
use Livewire\Component;
use Filament\Tables\Table;
use App\Helpers\Permission;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class PurchaseOrdersList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function table(Table $table): Table
    {
        return $table
            ->query(PurchaseOrder::query()->with('products'))
            ->heading('Purchase Orders')
            ->headerActions(
                hasPermission(Permission::PURCHASE_ORDERS_MANAGE) ? [
                    Action::make('create')->url(route('purchase_orders_create'))
                ] : [])
            ->columns([
                TextColumn::make('reference')->sortable()->searchable(),
                TextColumn::make('created_at')->dateTime('M jS Y h:ia')->sortable(),
                TextColumn::make('supplier.name')->searchable()->badge()->color('gray'),
                TextColumn::make('products')->state(function (PurchaseOrder $purchaseOrder) {
                    return $purchaseOrder->products->sum('quantity');
                }),
                TextColumn::make('cost')->state(function (PurchaseOrder $purchaseOrder) {
                    return $purchaseOrder->products->reduce(function (Money $carry, PurchaseOrderProduct $product) {
                        return $carry->add($product->cost->multiply($product->quantity));
                    }, Money::parse(0));
                }),
                TextColumn::make('status')->badge(),
                TextColumn::make('expected_delivery_date')->dateTime('M jS Y')->sortable(),
            ])
            ->recordUrl(function (PurchaseOrder $purchaseOrder) {
                if (hasPermission(Permission::PURCHASE_ORDERS_VIEW)) {
                    return route('purchase_orders_view', $purchaseOrder);
                }
            })
            ->filters([
                SelectFilter::make('Supplier')
                    ->multiple()
                    ->relationship('supplier', 'name')
                    ->preload(),
                SelectFilter::make('status')
                    ->multiple()
                    ->options(PurchaseOrderStatus::class)
                    ->default([PurchaseOrderStatus::Pending->value]),
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ])
            ->defaultSort('created_at', 'desc');
    }
}
