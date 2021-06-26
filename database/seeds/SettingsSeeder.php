<?php

namespace Database\Seeders;

use App\Models\Settings;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Settings::factory()->createMany([
            [
                'setting' => 'gst',
                'value' => '1.05',
            ],
            [
                'setting' => 'pst',
                'value' => '1.07',
            ],
        ]);
    }
}
