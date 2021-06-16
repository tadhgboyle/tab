<?php

namespace Database\Seeders;

use App\Models\Settings;
use App\Models\User;
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
            [
                'setting' => 'stats_time',
                'value' => '9999'
            ]
        ]);
    }
}
