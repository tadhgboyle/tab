<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Product;
use App\Models\Activity;
use App\Models\Category;
use App\Models\Settings;
use App\Models\UserLimits;
use App\Models\Transaction;
use App\Helpers\ProductHelper;
use App\Helpers\UserLimitsHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

// TODO: test with different limit durations (day/week)
class UserLimitsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the UserLimitsHelper::findSpent calculation is correct.
     */
    public function testFindSpentCalculationIsCorrect()
    {
        [$user, $food_category, $merch_category, $activities_category, $waterfront_category] = $this->createFakeRecords();

        $food_limit_info = UserLimitsHelper::getInfo($user, $food_category->id);
        $food_category_spent = UserLimitsHelper::findSpent($user, $food_category->id, $food_limit_info);

        $this->assertEquals(12.09, $food_category_spent);

        $merch_limit_info = UserLimitsHelper::getInfo($user, $merch_category->id);
        $merch_category_spent = UserLimitsHelper::findSpent($user, $merch_category->id, $merch_limit_info);

        $this->assertEquals(60.54, $merch_category_spent);

        $activities_limit_info = UserLimitsHelper::getInfo($user, $activities_category->id);
        $activities_category_spent = UserLimitsHelper::findSpent($user, $activities_category->id, $activities_limit_info);

        $this->assertEquals(6.29, $activities_category_spent);

        $waterfront_limit_info = UserLimitsHelper::getInfo($user, $waterfront_category->id);
        $waterfront_category_spent = UserLimitsHelper::findSpent($user, $waterfront_category->id, $waterfront_limit_info);
        // Special case, they have no limit set for the waterfront category
        $this->assertEquals(0, $waterfront_category_spent);
    }

    public function testFindSpentCalculationIsCorrectAfterItemReturn()
    {
        $this->assertTrue(true); // TODO after rewriting transaction handling
    }

    public function testFindSpentCalculationIsCorrectAfterTransactionReturn()
    {
        $this->assertTrue(true); // TODO after rewriting transaction handling
    }

    /**
     * Test that if a user has no limit (-1) set for a category, that they can spend as much as they want.
     */
    public function testUserCanSpendUnlimitedInCategory()
    {
        [$user, $food_category, $merch_category, $activities_category, $waterfront_category] = $this->createFakeRecords();

        $can_spend_1_million_merch = UserLimitsHelper::canSpend($user, 1000000.00, $merch_category->id);
        // This should be true as their merch category is unlimited
        $this->assertTrue($can_spend_1_million_merch);
    }

    /**
     * Test that if a limit is set on a category, that a user cannot spend more than that limit.
     * Tests canSpend function in UserLimitsHelper.
     */
    public function testUserCannotSpendOverLimitInCategory()
    {
        [$user, $food_category, $merch_category, $activities_category, $waterfront_category] = $this->createFakeRecords();

        $can_spend_1_dollar_food = UserLimitsHelper::canSpend($user, 1.00, $food_category->id);
        // This should be true, as they've only spent 12.09 / 15.00 dollars, and another 1 dollar would not go past 15.
        $this->assertTrue($can_spend_1_dollar_food);

        $can_spend_12_dollars_food = UserLimitsHelper::canSpend($user, 12.00, $food_category->id);
        // This should be false, as they've spent 12.09 / 15.00, and another 12 dollars would go past 15
        $this->assertFalse($can_spend_12_dollars_food);

        $can_spent_3_dollars_activities = UserLimitsHelper::canSpend($user, 3.00, $activities_category->id);
        // This should be true, as they spent 6.29 / 10, and another 3 would not go over 10
        $this->assertTrue($can_spent_3_dollars_activities);

        $can_spent_5_dollars_activities = UserLimitsHelper::canSpend($user, 5.00, $activities_category->id);
        // This should be false, as they spent 6.29 / 10, and another 5 would not go over 10
        $this->assertFalse($can_spent_5_dollars_activities);

        $can_spent_10_dollars_waterfront = UserLimitsHelper::canSpend($user, 10, $waterfront_category->id);
        // This should be true, since they have no explicit limit set it defaults to unlimited
        $this->assertTrue($can_spent_10_dollars_waterfront);
    }

    /**
     * Creates the following records in db:
     * - Food category (5$ a day)
     * - Merch category (unlimited)
     * - Fake role for fake user
     * - Fake User
     * - UserLimits for the fake user for each category (one is unlimited, one is limited)
     * - Fake transactions for the fake user.
     */
    private function createFakeRecords()
    {
        $role = Role::factory()->create();

        $user = User::factory()->create([
            'role_id' => $role->id
        ]);

        Settings::factory()->createMany([
            [
                'setting' => 'gst',
                'value' => '1.05',
                'editor_id' => $user->id
            ],
            [
                'setting' => 'pst',
                'value' => '1.07',
                'editor_id' => $user->id
            ]
        ]);

        [$food_category, $merch_category, $activities_category, $waterfront_category] = $this->createFakeCategories();

        UserLimits::factory()->create([
            'user_id' => $user->id,
            'category_id' => $food_category->id,
            'limit_per' => 15,
            'duration' => 0
        ]);

        UserLimits::factory()->create([
            'user_id' => $user->id,
            'category_id' => $merch_category->id
        ]);

        UserLimits::factory()->create([
            'user_id' => $user->id,
            'category_id' => $activities_category->id,
            'limit_per' => 10
        ]);

        [$skittles, $sweater, $coffee, $hat] = $this->createFakeProducts($food_category->id, $merch_category->id);
        [$widegame] = $this->createFakeActivities($activities_category);

        Transaction::factory()->create([
            'purchaser_id' => $user->id,
            'cashier_id' => $user->id,
            'products' => implode(', ', [
                ProductHelper::serializeProduct($skittles->id, 2, $skittles->price, 1.05, 'null', 0),
                ProductHelper::serializeProduct($hat->id, 1, $hat->price, 1.05, 'null', 0)
            ]),
            'total_price' => 3.15 // TODO
        ]);

        Transaction::factory()->create([
            'purchaser_id' => $user->id,
            'cashier_id' => $user->id,
            'products' => implode(', ', [
                ProductHelper::serializeProduct($sweater->id, 1, $sweater->price, 1.05, 1.07, 0),
                ProductHelper::serializeProduct($coffee->id, 2, $coffee->price, 1.05, 1.07, 0)
            ]),
            'total_price' => 44.79 // TODO
        ]);

        DB::table('activity_transactions')->insert([
            'user_id' => $user->id,
            'cashier_id' => $user->id,
            'activity_id' => $widegame->id,
            'activity_price' => $widegame->getPrice(),
            'activity_gst' => 1.05,
            'returned' => false,
            'created_at' => now()
        ]);

        // TODO: General category with hat and widegame on it

        return [$user, $food_category, $merch_category, $activities_category, $waterfront_category];
    }

    /** @return Category[] */
    private function createFakeCategories()
    {
        $food_category = Category::factory()->create([
            'name' => 'Food'
        ]);

        $merch_category = Category::factory()->create([
            'name' => 'Merch'
        ]);

        $activities_category = Category::factory()->create([
            'name' => 'Activities',
            'type' => 3
        ]);

        $waterfront_category = Category::factory()->create([
            'name' => 'Waterfront'
        ]);

        return [$food_category, $merch_category, $activities_category, $waterfront_category];
    }

    /** @return Product[] */
    private function createFakeProducts($food_category_id, $merch_category_id)
    {
        $skittles = Product::factory()->create([
            'name' => 'Skittles',
            'price' => 1.50,
            'pst' => false,
            'category_id' => $food_category_id
        ]);

        $sweater = Product::factory()->create([
            'name' => 'Sweater',
            'price' => 39.99,
            'pst' => true,
            'category_id' => $merch_category_id
        ]);

        $coffee = Product::factory()->create([
            'name' => 'Coffee',
            'price' => 3.99,
            'pst' => true,
            'category_id' => $food_category_id
        ]);

        $hat = Product::factory()->create([
            'name' => 'Hat',
            'price' => 15.00,
            'pst' => false,
            'category_id' => $merch_category_id
        ]);

        return [$skittles, $sweater, $coffee, $hat];
    }

    /** @return Activity[] */
    private function createFakeActivities($activities_category)
    {
        $widegame = Activity::factory()->create([
            'name' => 'Widegame',
            'price' => 5.99,
            'pst' => true,
            'category_id' => $activities_category->id
        ]);

        return [$widegame];
    }
}
