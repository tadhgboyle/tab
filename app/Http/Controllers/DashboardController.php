<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityRegistration;
use App\Models\GiftCard;
use App\Models\GiftCardAdjustment;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderProductReturn;
use App\Models\OrderReturn;
use App\Models\Product;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        return view('pages.dashboard', [
            'users' => $this->getUserData(),
            'financial' => $this->getFinancialData(),
            'activities' => $this->getActivityData(),
            'products' => $this->getProductData(),
            'giftCards' => $this->getGiftCardData(),
            'alerts' => $this->getAlerts(),
        ]);
    }

    private function getUserData(): Collection
    {
        $data = collect();

        // Count of users
        $data['total'] = User::count();
        // Count of users excluding staff
        $data['totalExcludingStaff'] = User::whereHas('role', function ($query) {
            $query->where('staff', false);
        })->count();
        // Count of active users (users with an order in the last week)
        $data['active'] = User::whereHas('orders', function ($query) {
            $query->where('created_at', '>=', now()->subWeek());
        })->count();
        // Count of inactive users (users without an order in the last week)
        $data['inactive'] = User::whereDoesntHave('orders', function ($query) {
            $query->where('created_at', '>=', now()->subWeek());
        })->count();
        // Count of new users (users who signed up in the last week)
        $data['new'] = User::where('created_at', '>=', now()->subWeek())->count();
        // Top users by spending across orders and activities (with their # of orders and activities)
        // Top cashiers by revenue (with their # of orders/activities)

        return $data;
    }

    // TODO Allow selecting rotation
    private function getFinancialData(): Collection
    {
        $data = collect();

        // Total amount of unspent user balance
        $data['unspentUserBalance'] = Money::parse(User::sum('balance'));
        // Total amount of unspent gift card balance
        $data['unspentGiftCardBalance'] = Money::parse(GiftCard::sum('remaining_balance'));
        // Total revenue from orders
        $data['orderRevenue'] = Money::parse(Order::sum('total_price'));
        // Total revenue from activities
        $data['activityRevenue'] = Money::parse(ActivityRegistration::sum('total_price'));
        // Total revenue across orders and activities
        $data['totalRevenue'] = $data['orderRevenue']->add($data['activityRevenue']);
        // Total revenue from gift cards
        // TODO Subtract refunds?
        $data['giftCardRevenue'] = Money::parse(GiftCardAdjustment::where('type', GiftCardAdjustment::TYPE_CHARGE)->sum('amount'));
        // Average order value
        $data['averageOrderValue'] = Money::parse(Order::avg('total_price'))->divide(100);
        // Average cash payment value
        $data['averageCashPaymentValue'] = Money::parse(Order::avg('purchaser_amount'))->divide(100);
        // Average gift card value
        $data['averageGiftCardValue'] = Money::parse(Order::avg('gift_card_amount'))->divide(100);
        // Average activity value
        $data['averageActivityValue'] = Money::parse(ActivityRegistration::avg('total_price'))->divide(100);
        // Total revenue lost from returns
        $productReturns = Money::parse(OrderProductReturn::sum('total_return_amount'));
        $orderReturns = Money::parse(OrderReturn::where('caused_by_product_return', false)->sum('total_return_amount'));
        $data['returnRevenue'] = $productReturns->add($orderReturns);
        // Total revenue lost from activity cancellations
        $data['activityCancellationRevenue'] = Money::parse(ActivityRegistration::where('returned', true)->sum('total_price'));

        return $data;
    }

    // TODO Allow selecting rotation
    private function getActivityData(): Collection
    {
        $data = collect();

        // Upcoming activities (in the next week)
        $data['upcoming'] = Activity::where('start', '>=', now())
            ->orderBy('start')
            ->limit(10)
            ->get();
        // Activities with the most signups
        $data['mostSignups'] = ActivityRegistration::selectRaw('activity_id, count(*) as count')
            ->groupBy('activity_id')
            ->orderByDesc('count')
            ->limit(10)
            ->get();
        // Activities with the most revenue
        $data['mostRevenue'] = ActivityRegistration::selectRaw('activity_id, sum(total_price) as revenue')
            ->groupBy('activity_id')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();
        // Activities with the most cancellations
        $data['mostCancellations'] = ActivityRegistration::selectRaw('activity_id, count(*) as count')
            ->where('returned', true)
            ->groupBy('activity_id')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return $data;
    }

    // TODO Allow selecting rotation
    private function getProductData(): Collection
    {
        $data = collect();

        // Products with the most sales
        // TODO Subtract returns?
        $data['mostSales'] = OrderProduct::without('product')->selectRaw('product_id, sum(quantity) as sales')
            ->groupBy('product_id')
            ->orderByDesc('sales')
            ->limit(10)
            ->get();
        // Products with the most returns
        $data['mostReturns'] = OrderProduct::without('product')->selectRaw('product_id, sum(returned) as returns')
            ->groupBy('product_id')
            ->orderByDesc('returns')
            ->limit(10)
            ->get();
        // Products with the most revenue
        // Products with the most revenue lost from returns

        return $data;
    }

    private function getGiftCardData(): Collection
    {
        $data = collect();

        // Total number of gift cards
        $data['total'] = GiftCard::count();
        // Total amount of gift card unused balance
        $data['unusedBalance'] = GiftCard::sum('remaining_balance');
        // Gift cards with the most remaining balance
        $data['mostRemainingBalance'] = GiftCard::orderByDesc('remaining_balance')
            ->limit(10)
            ->get();
        // Gift cards with the most revenue
        // Gift cards with the most revenue lost from refunds
        // Percent of gift cards used
        $data['percentUsed'] = GiftCard::where('remaining_balance', '>', 0)->count() / GiftCard::count() * 100;

        return $data;
    }

    private function getAlerts(): Collection
    {
        $data = collect();

        // Gift cards about to expire
        $data['expiringGiftCards'] = GiftCard::where('expires_at', '<=', now()->addWeek())
            ->orderBy('expires_at')
            ->limit(10)
            ->get();
        // Products with low stock
        $data['lowStockProducts'] = Product::where('stock', '<=', 10)
            ->orderBy('stock')
            ->limit(10)
            ->get();
        // TODO something like most recently out of stock?
        // TODO Activities with low capacity
        $data['lowCapacityActivities'] = null;
        
        return $data;
    }
}
