<?php

namespace Database\Factories;

use App\Enums\PurchaseOrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(PurchaseOrderStatus::cases());

        $expectedDeliveryDate = $this->faker->dateTimeBetween('now', '+1 year');
        $actualDeliveryDate = $status === PurchaseOrderStatus::Completed ? $this->faker->dateTimeBetween('now', $expectedDeliveryDate) : null;

        return [
            'reference' => $this->faker->unique()->numerify('PO#####'),
            'expected_delivery_date' => $expectedDeliveryDate,
            'delivery_date' => $actualDeliveryDate,
            'status' => $status,
        ];
    }
}
