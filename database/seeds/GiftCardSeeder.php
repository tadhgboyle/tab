<?php

namespace Database\Seeders;

use App\Models\GiftCard;
use Illuminate\Database\Seeder;

class GiftCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        GiftCard::factory()->count(20)->create();
    }
}
