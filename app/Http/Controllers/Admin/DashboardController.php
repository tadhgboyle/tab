<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Order;
use App\Models\Payout;
use Cknow\Money\Money;
use App\Models\Product;
use App\Models\Activity;
use App\Models\GiftCard;
use App\Enums\PayoutStatus;
use App\Helpers\Permission;
use App\Models\OrderReturn;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\GiftCardAdjustment;
use App\Models\OrderProductReturn;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use App\Models\ActivityRegistration;
use App\Enums\GiftCardAdjustmentType;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = [];

        if (hasPermission(Permission::DASHBOARD_USERS)) {
            $data['users'] = $this->getUserData();
        }

        if (hasPermission(Permission::DASHBOARD_FINANCIAL)) {
            $data['financial'] = $this->getFinancialData();
        }

        if (hasPermission(Permission::DASHBOARD_ACTIVITIES)) {
            $data['activities'] = $this->getActivityData();
        }

        if (hasPermission(Permission::DASHBOARD_PRODUCTS)) {
            $data['products'] = $this->getProductData();
        }

        if (hasPermission(Permission::DASHBOARD_GIFT_CARDS)) {
            $data['giftCards'] = $this->getGiftCardData();
        }

        if (hasPermission(Permission::DASHBOARD_ALERTS)) {
            $data['alerts'] = $this->getAlerts();
        }

        return view('pages.admin.dashboard', $data);
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
        $data['topSpending'] = User::query()
            ->withCount(['orders', 'activityRegistrations'])
            ->limit(10)
            ->get();
        // Top cashiers by revenue (with their # of orders/activities)
        $data['topCashiers'] = User::query()
            ->withCount(['brokeredOrders', 'brokeredActivityRegistrations'])
            ->having('brokered_orders_count', '>', 0)
            ->orHaving('brokered_activity_registrations_count', '>', 0)
            ->withSum('brokeredOrders', 'total_price')
            ->withSum('brokeredActivityRegistrations', 'total_price')
            ->limit(10)
            ->orderBy('brokered_orders_sum_total_price', 'desc')
            ->orderBy('brokered_activity_registrations_sum_total_price', 'desc')
            ->get()
            ->map(function ($cashier) {
                $cashier->brokered_orders_sum_total_price = Money::parse($cashier->brokered_orders_sum_total_price);
                $cashier->brokered_activity_registrations_sum_total_price = Money::parse($cashier->brokered_activity_registrations_sum_total_price);
                $cashier->total_revenue = $cashier->brokered_orders_sum_total_price->add($cashier->brokered_activity_registrations_sum_total_price);
                return $cashier;
            })
            ->sortByDesc('total_revenue');
        // Top spenders
        $data['topSpenders'] = User::query()
            ->withCount(['orders', 'activityRegistrations'])
            ->having('orders_count', '>', 0)
            ->orHaving('activity_registrations_count', '>', 0)
            ->withSum('orders', 'total_price')
            ->withSum('activityRegistrations', 'total_price')
            ->orderBy('orders_sum_total_price', 'desc')
            ->orderBy('activity_registrations_sum_total_price', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($user) {
                $user->orders_sum_total_price = Money::parse($user->orders_sum_total_price);
                $user->activity_registrations_sum_total_price = Money::parse($user->activity_registrations_sum_total_price);
                $user->total_revenue = $user->orders_sum_total_price->add($user->activity_registrations_sum_total_price);
                return $user;
            })
            ->sortByDesc('total_revenue');

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
        // Average margin across orders
        $data['averageMargin'] = round(\Illuminate\Support\Facades\DB::select(
            'SELECT AVG(price - cost) as average_margin FROM order_products'
        )[0]->average_margin / 100, 2);
        // Total revenue from activities
        $data['activityRevenue'] = Money::parse(ActivityRegistration::sum('total_price'));
        // Total revenue across orders and activities
        $data['totalRevenue'] = $data['orderRevenue']->add($data['activityRevenue']);
        // Total revenue from gift cards
        // TODO Subtract refunds?
        $data['giftCardRevenue'] = Money::parse(GiftCardAdjustment::where('type', GiftCardAdjustmentType::Charge)->sum('amount'));
        // Average order value
        $data['averageOrderValue'] = Money::parse(Order::avg('total_price'))->divide(100);
        // Taxes collected
        $data['taxesCollected'] = Money::parse(Order::sum('total_tax'));
        // Average cash payment value
        $data['averageCashPaymentValue'] = Money::parse(Order::avg('purchaser_amount'))->divide(100);
        // Average gift card value
        $data['averageGiftCardValue'] = Money::parse(Order::avg('gift_card_amount'))->divide(100);
        // Average activity value
        $data['averageActivityValue'] = Money::parse(ActivityRegistration::avg('total_price'))->divide(100);
        // Total revenue lost from returns
        $productReturns = Money::parse(OrderProductReturn::sum('total_return_amount'));
        $orderReturns = Money::parse(OrderReturn::where('caused_by_product_return', false)->sum('total_return_amount'));
        $data['returnedOrderRevenue'] = $productReturns->add($orderReturns);
        // TODO Total returns to gift cards
        // Total revenue lost from activity cancellations
        $data['activityCancellationRevenue'] = Money::parse(ActivityRegistration::where('returned', true)->sum('total_price'));
        // Total paid out
        $data['totalPayouts'] = Money::parse(Payout::where('status', PayoutStatus::Paid)->sum('amount'));
        // Recent payouts
        // TODO Allow selecting rotation
        $data['recentPayouts'] = Payout::orderByDesc('created_at')->where('status', PayoutStatus::Paid)->limit(10)->get();

        return $data;
    }

    // TODO Allow selecting rotation
    private function getActivityData(): Collection
    {
        $data = collect();

        // Upcoming activities (in the next week)
        $data['upcoming'] = Activity::where('start', '>=', now())
            ->withCount('registrations')
            ->orderBy('start')
            ->limit(10)
            ->get();
        // Activities with the most signups
        $data['mostSignups'] = Activity::query()
            ->withCount('registrations')
            ->orderByDesc('registrations_count')
            ->limit(10)
            ->get();
        // Activities with the most revenue
        $data['mostRevenue'] = ActivityRegistration::selectRaw('activity_id, sum(total_price) as revenue')
            ->groupBy('activity_id')
            ->orderByDesc('revenue')
            ->with('activity')
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
        $data['mostSales'] = OrderProduct::selectRaw('product_id, sum(quantity) as sales')
            ->groupBy('product_id')
            ->orderByDesc('sales')
            ->with('product')
            ->limit(10)
            ->get();
        // Products with the most returns
        $data['mostReturns'] = OrderProduct::selectRaw('product_id, sum(returned) as returns')
            ->groupBy('product_id')
            ->orderByDesc('returns')
            ->with('product')
            ->limit(10)
            ->get();
        // Products with the most revenue

        // TODO store total_price on OrderProduct
        // TODO store total_tax on OrderProduct and Order

        // Products with the most revenue lost from returns

        // Product inventory value
        $inventoryableProducts = Product::query()->where('unlimited_stock', false);
        $productVariants = ProductVariant::query()->whereIn('product_id', $inventoryableProducts->pluck('id'));
        $inventoryValue = Money::parse($inventoryableProducts->sum('stock'))->multiply($inventoryableProducts->avg('price'));
        $inventoryValue = $inventoryValue->add(Money::parse($productVariants->sum('stock'))->multiply($productVariants->avg('cost')));
        $data['inventoryValue'] = $inventoryValue;

        // TODO Margin over time

        return $data;
    }

    private function getGiftCardData(): Collection
    {
        $data = collect();

        // Total number of gift cards
        $data['total'] = GiftCard::count();
        // Total amount of gift card unused balance
        $data['unusedBalance'] = Money::parse(GiftCard::sum('remaining_balance'));
        // Gift cards with the most remaining balance
        $data['mostRemainingBalance'] = GiftCard::orderByDesc('remaining_balance')
            ->limit(10)
            ->get();
        // Gift cards with the most revenue
        // Gift cards with the most revenue lost from refunds
        // Percent of total gift card value used
        $data['percentUsed'] = GiftCard::count() === 0 ? 0 : GiftCard::where('remaining_balance', '>', 0)->count() / GiftCard::count() * 100;

        return $data;
    }

    private function getAlerts(): Collection
    {
        $data = collect();

        // Gift cards about to expire
        $data['expiringGiftCards'] = GiftCard::query()
            ->where('expires_at', '<=', now()->addWeek())
            ->orderBy('expires_at')
            ->limit(10)
            ->get();
        // Products with low stock
        // TODO: support variants
        $data['lowStockProducts'] = Product::query()
            ->where('unlimited_stock', false)
            ->where('stock', '<=', 10)
            ->orderBy('stock')
            ->limit(10)
            ->get();
        // TODO something like most recently out of stock?
        // TODO Activities with low capacity

        return $data;
    }
}
