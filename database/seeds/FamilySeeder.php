<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Family;
use App\Enums\FamilyMemberRole;
use Illuminate\Database\Seeder;

class FamilySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $family = Family::factory()->create([
            'name' => 'Boyles',
        ]);

        $family->members()->createMany([
            [
                'user_id' => 1,
                'role' => FamilyMemberRole::Admin,
            ],
            [
                'user_id' => 2,
                'role' => FamilyMemberRole::Admin,
            ],
            [
                'user_id' => 3,
                'role' => FamilyMemberRole::Member,
            ],
        ]);

        $families = Family::factory()->count(10)->create();

        $families->each(function (Family $family) {
            // find user who is not in any family
            $users = User::whereDoesntHave('family')->get();
            $admin = $users->random(1)->first();

            $family->members()->create([
                'user_id' => $admin->id,
                'role' => FamilyMemberRole::Admin,
            ]);

            $family->update([
                'name' => explode(' ', $admin->full_name)[1] . 's',
            ]);

            $family->members()->createMany(
                $users->random(random_int(1, 5))->map(function (User $user) {
                    return [
                        'user_id' => $user->id,
                        'role' => FamilyMemberRole::Member,
                    ];
                })->toArray()
            );
        });

        $families->each(function (Family $family) {
            if (random_int(0, 3) === 0) {
                $family->members->random(random_int(1, 3))->map->delete();
            }
        });
    }
}
