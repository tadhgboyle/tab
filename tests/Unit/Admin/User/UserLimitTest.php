<?php

namespace Tests\Unit\Admin\User;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Order;
use Cknow\Money\Money;
use App\Models\Product;
use App\Models\Activity;
use App\Models\Category;
use App\Models\Settings;
use App\Models\UserLimit;
use App\Enums\CategoryType;
use App\Models\OrderProduct;
use App\Helpers\RotationHelper;
use App\Enums\UserLimitDuration;
use Database\Seeders\RotationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Activities\ActivityRegistrationCreateService;

// TODO: test with different limit durations (day/week)
// TODO: test when categories are made after user is made
// TODO: test after changing tax rates to ensure it is using historical data
class UserLimitTest extends TestCase
{
    use RefreshDatabase;

    public function testIsUnlimited(): void
    {
        $user = User::factory()->create([
            'role_id' => Role::factory()->create()->id,
        ]);
        $category = Category::factory()->create();

        $user_limit = UserLimit::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'limit' => -1_00
        ]);

        $this->assertTrue($user_limit->isUnlimited());

        $user_limit->update([
            'limit' => 15_00,
        ]);

        $this->assertFalse($user_limit->isUnlimited());
    }

    public function testFindSpentCalculationIsCorrect(): void
    {
        [$user, $food_category, $merch_category, $activities_category, $waterfront_category] = $this->createFakeRecords();
        $user = $user->refresh();

        $this->assertEquals(Money::parse(12_09), $user->limitFor($food_category)->findSpent());

        $this->assertEquals(Money::parse(60_54), $user->limitFor($merch_category)->findSpent());

        $this->assertEquals(Money::parse(6_71), $user->limitFor($activities_category)->findSpent());

        // Special case, they have no limit set for the waterfront category
        $this->assertEquals(Money::parse(0_00), $user->limitFor($waterfront_category)->findSpent());
    }

    public function testFindSpentCalculationIsCorrectAfterItemReturn(): void
    {
        $this->markTestIncomplete();
    }

    public function testFindSpentCalculationIsCorrectAfterOrderReturn(): void
    {
        $this->markTestIncomplete();
    }

    public function testFindSpentCalculationIsCorrectAfterActivityReturn(): void
    {
        $this->markTestIncomplete();
    }

    public function testUserCanSpendUnlimitedInCategoryIfNegativeOneIsLimit(): void
    {
        [$user, , $merch_category] = $this->createFakeRecords();

        $this->assertTrue($user->limitFor($merch_category)->canSpend(Money::parse(1_000_000_00)));
    }

    public function testCanSpendCalculationIsCorrect(): void
    {
        [$user, $food_category, , $activities_category, $waterfront_category] = $this->createFakeRecords();
        $user = $user->refresh();

        // This should be true, as they've only spent 12.09 / 15.00 dollars, and another 1 dollar would not go past 15.
        $this->assertTrue(
            $user->limitFor($food_category)->canSpend(Money::parse(1_00))
        );

        // This should be false, as they've spent 12.09 / 15.00, and another 12 dollars would go past 15
        $this->assertFalse(
            $user->limitFor($food_category)->canSpend(Money::parse(12_00))
        );

        // This should be true, as they spent 6.29 / 10, and another 3 would not go over 10
        $this->assertTrue(
            $user->limitFor($activities_category)->canSpend(Money::parse(3_00))
        );

        // This should be false, as they spent 6.29 / 10, and another 5 would not go over 10
        $this->assertFalse(
            $user->limitFor($activities_category)->canSpend(Money::parse(5_00))
        );

        // This should be true, since they have no explicit limit set it defaults to unlimited
        $this->assertTrue(
            $user->limitFor($waterfront_category)->canSpend(Money::parse(10_00))
        );
    }

    /** @return Role[] */
    private function createRoles(): array
    {
        $superadmin_role = Role::factory()->create();

        $camper_role = Role::factory()->create([
            'name' => 'Camper',
            'staff' => false,
            'superuser' => false,
            'order' => 2
        ]);

        return [$superadmin_role, $camper_role];
    }

    private function createSuperadminUser(Role $superadmin_role): User
    {
        return User::factory()->create([
            'role_id' => $superadmin_role->id
        ]);
    }

    /**
     * Creates the following records in db:
     * - Food category (5$ a day)
     * - Merch category (unlimited)
     * - Fake role for fake user
     * - Fake User
     * - UserLimit for the fake user for each category (one is unlimited, one is limited)
     * - Fake orders for the fake user.
     */
    private function createFakeRecords(): array
    {
        app(RotationSeeder::class)->run();

        [$superadmin_role] = $this->createRoles();

        $user = $this->createSuperadminUser($superadmin_role);

        Settings::factory()->createMany([
            [
                'setting' => 'gst',
                'value' => '5.00',
            ],
            [
                'setting' => 'pst',
                'value' => '7.00',
            ]
        ]);

        [$food_category, $merch_category, $activities_category, $waterfront_category] = $this->createFakeCategories();

        UserLimit::factory()->create([
            'user_id' => $user->id,
            'category_id' => $food_category->id,
            'limit' => 15_00,
            'duration' => UserLimitDuration::Daily
        ]);

        UserLimit::factory()->create([
            'user_id' => $user->id,
            'category_id' => $merch_category->id,
            'limit' => -1_00
        ]);

        UserLimit::factory()->create([
            'user_id' => $user->id,
            'category_id' => $activities_category->id,
            'limit' => 10_00
        ]);

        [$skittles, $sweater, $coffee, $hat] = $this->createFakeProducts($food_category->id, $merch_category->id);
        [$widegame] = $this->createFakeActivities($activities_category);

        $order1 = Order::factory()->create([
            'purchaser_id' => $user->id,
            'cashier_id' => $user->id,
            'rotation_id' => resolve(RotationHelper::class)->getCurrentRotation()->id,
            'total_price' => 3_15, // TODO
            'purchaser_amount' => 3_15,
            'gift_card_amount' => 0_00,
        ]);

        $skittles_product = OrderProduct::from($skittles, null, 2, 5);
        $skittles_product->order_id = $order1->id;
        $hat_product = OrderProduct::from($hat, null, 1, 5);
        $hat_product->order_id = $order1->id;

        $order1->products()->saveMany([
            $skittles_product,
            $hat_product,
        ]);

        $order2 = Order::factory()->create([
            'purchaser_id' => $user->id,
            'cashier_id' => $user->id,
            'rotation_id' => resolve(RotationHelper::class)->getCurrentRotation()->id,
            'total_price' => 44_79, // TODO
            'purchaser_amount' => 44_79,
            'gift_card_amount' => 0_00,
        ]);

        $sweater_product = OrderProduct::from($sweater, null, 1, 5, 7);
        $sweater_product->order_id = $order2->id;
        $coffee_product = OrderProduct::from($coffee, null, 2, 5, 7);
        $coffee_product->order_id = $order2->id;

        $order2->products()->saveMany([
            $sweater_product,
            $coffee_product,
        ]);

        $this->actingAs($user);
        new ActivityRegistrationCreateService($widegame, $user);

        // TODO: General category with hat and widegame on it

        return [$user, $food_category, $merch_category, $activities_category, $waterfront_category];
    }

    /** @return Category[] */
    private function createFakeCategories(): array
    {
        $food_category = Category::factory()->create([
            'name' => 'Food'
        ]);

        $merch_category = Category::factory()->create([
            'name' => 'Merch'
        ]);

        $activities_category = Category::factory()->create([
            'name' => 'Activities',
            'type' => CategoryType::Activities
        ]);

        $waterfront_category = Category::factory()->create([
            'name' => 'Waterfront'
        ]);

        return [$food_category, $merch_category, $activities_category, $waterfront_category];
    }

    /** @return Product[] */
    private function createFakeProducts($food_category_id, $merch_category_id): array
    {
        $skittles = Product::factory()->create([
            'name' => 'Skittles',
            'price' => 1_50,
            'pst' => false,
            'category_id' => $food_category_id
        ]);

        $sweater = Product::factory()->create([
            'name' => 'Sweater',
            'price' => 39_99,
            'pst' => true,
            'category_id' => $merch_category_id
        ]);

        $coffee = Product::factory()->create([
            'name' => 'Coffee',
            'price' => 3_99,
            'pst' => true,
            'category_id' => $food_category_id
        ]);

        $hat = Product::factory()->create([
            'name' => 'Hat',
            'price' => 15_00,
            'pst' => false,
            'category_id' => $merch_category_id
        ]);

        return [$skittles, $sweater, $coffee, $hat];
    }

    /** @return Activity[] */
    private function createFakeActivities($activities_category): array
    {
        $widegame = Activity::factory()->create([
            'name' => 'Widegame',
            'price' => 5_99,
            'pst' => true,
            'category_id' => $activities_category->id
        ]);

        return [$widegame];
    }
}
