<?php

namespace Tests\Unit\Payout;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use Cknow\Money\Money;
use App\Models\Product;
use App\Models\Category;
use App\Models\Settings;
use Illuminate\Http\Request;
use App\Http\Requests\PayoutRequest;
use Database\Seeders\RotationSeeder;
use App\Services\Payouts\PayoutCreateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Orders\OrderCreateService;

class PayoutCreateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testCantMakePayoutIfNothingOwing(): void
    {
        [$user] = $this->createData(false);

        $payoutService = new PayoutCreateService(new PayoutRequest([
            'identifier' => '#1',
            'amount' => 10_00,
        ]), $user);

        $this->assertSame(PayoutCreateService::RESULT_NOTHING_OWED, $payoutService->getResult());
        $this->assertStringContainsString('User does not owe anything.', $payoutService->getMessage());
    }

    public function testCanCreatePayout(): void
    {
        [$user, $admin] = $this->createData();

        $payoutService = new PayoutCreateService(new PayoutRequest([
            'identifier' => '#1',
            'amount' => 10_00,
        ]), $user);

        $this->assertSame(PayoutCreateService::RESULT_SUCCESS, $payoutService->getResult());

        $payout = $payoutService->getPayout();

        $this->assertEquals(Money::parse(10_00), $payout->amount);
        $this->assertEquals(Money::parse(5_00), $user->findOwing());
        $this->assertSame('#1', $payout->identifier);
        $this->assertSame($admin->id, $payout->cashier->id);
        $this->assertSame($user->id, $payout->user->id);
    }

    public function testUserOwingCalculatedCorrectlyAfterPayoutCreation(): void
    {
        [$user] = $this->createData();

        $owing_before_payout = $user->findOwing();

        $payoutService = new PayoutCreateService(new PayoutRequest([
            'identifier' => '#1',
            'amount' => 10_00,
        ]), $user);

        $this->assertSame(PayoutCreateService::RESULT_SUCCESS, $payoutService->getResult());
        $this->assertStringContainsString("Successfully created payout of $10.00 for {$user->full_name}", $payoutService->getMessage());

        $payout = $payoutService->getPayout();
        $this->assertEquals($owing_before_payout->subtract($payout->amount), $user->refresh()->findOwing());
    }

    /** @return User[] */
    private function createData(bool $order = true): array
    {
        app(RotationSeeder::class)->run();

        $role = Role::factory()->create();
        $user = User::factory()->create([
            'role_id' => $role->id,
        ]);

        $admin = User::factory()->create([
            'role_id' => $role->id,
        ]);

        $this->actingAs($admin);

        if ($order) {
            $category = Category::factory()->create();
            $product = Product::factory()->create([
                'category_id' => $category->id,
                'pst' => 0,
                'price' => 5_00,
            ]);
            Settings::insert([
                'setting' => 'gst',
                'value' => 0,
            ]);

            new OrderCreateService(new Request([
                'products' => json_encode([['id' => $product->id, 'quantity' => 1]]),
            ]), $user);
        }

        return [$user, $admin];
    }
}
