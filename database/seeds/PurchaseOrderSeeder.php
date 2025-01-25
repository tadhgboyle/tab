<?php

namespace Database\Seeders;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderProduct;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class PurchaseOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Supplier::each(function (Supplier $supplier) {
            foreach (range(1, random_int(1, 5)) as $i) {
                $purchaseOrder = PurchaseOrder::factory()->state([
                    'supplier_id' => $supplier->id,
                ])->create();

                $state = [
                    'purchase_order_id' => $purchaseOrder->id,
                ];

                if (collect([PurchaseOrderStatus::Cancelled, PurchaseOrderStatus::Draft, PurchaseOrderStatus::Pending])->contains($purchaseOrder->status)) {
                    $state['received_quantity'] = 0;
                }

                $purchaseOrder->products()->saveMany(
                    PurchaseOrderProduct::factory()->state($state)->count(random_int(1, 10))->create()
                );
            }
        });
    }
}
