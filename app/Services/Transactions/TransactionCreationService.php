<?php

namespace App\Services\Transactions;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Services\Service;
use App\Helpers\TaxHelper;
use App\Helpers\Permission;
use App\Models\Transaction;
use Cknow\Money\Money;
use Illuminate\Http\Request;
use App\Helpers\RotationHelper;
use App\Helpers\SettingsHelper;
use App\Helpers\UserLimitsHelper;
use App\Models\TransactionProduct;
use Illuminate\Support\Collection;
use Illuminate\Http\RedirectResponse;

class TransactionCreationService extends Service
{
    use TransactionService;

    public const RESULT_NO_SELF_PURCHASE = 0;
    public const RESULT_NO_ITEMS_SELECTED = 1;
    public const RESULT_NEGATIVE_QUANTITY = 2;
    public const RESULT_NO_STOCK = 3;
    public const RESULT_NOT_ENOUGH_BALANCE = 4;
    public const RESULT_NOT_ENOUGH_CATEGORY_BALANCE = 5;
    public const RESULT_NO_CURRENT_ROTATION = 6;
    public const RESULT_SUCCESS = 7;

    public function __construct(Request $request, User $purchaser)
    {
        if (resolve(RotationHelper::class)->getCurrentRotation() === null) {
            $this->_result = self::RESULT_NO_CURRENT_ROTATION;
            $this->_message = 'Cannot create transaction with no current rotation.';
            return;
        }

        if (!hasPermission(Permission::CASHIER_SELF_PURCHASES) && $purchaser->id === auth()->id()) {
            $this->_result = self::RESULT_NO_SELF_PURCHASE;
            $this->_message = 'You cannot make purchases for yourself.';
            return;
        }

        $order_products = collect(json_decode($request->get('products')));

        if (!$order_products->count()) {
            $this->_result = self::RESULT_NO_ITEMS_SELECTED;
            $this->_message = 'Please select at least one item.';
            return;
        }

        $settingsHelper = resolve(SettingsHelper::class);

        /** @var Collection<TransactionProduct> */
        $transaction_products = Collection::make();

        $total_price = Money::parse(0);

        foreach ($order_products->all() as $product_meta) {
            $id = $product_meta->id;
            $quantity = $product_meta->quantity;

            $product = Product::find($id);

            if ($quantity < 1) {
                $this->_result = self::RESULT_NEGATIVE_QUANTITY;
                $this->_message = "Quantity must be >= 1 for item {$product->name}";
                return;
            }

            // Stock handling
            if (!$product->hasStock($quantity)) {
                $this->_result = self::RESULT_NO_STOCK;
                $this->_message = "Not enough {$product->name} in stock. Only {$product->stock} remaining.";
                return;
            }

            $transaction_products->add(TransactionProduct::from(
                $product,
                $quantity,
                $settingsHelper->getGst(),
                $product->pst ? $settingsHelper->getPst() : null
            ));

            $total_price = $total_price->add(TaxHelper::calculateFor($product->price, $quantity, $product->pst));
        }

        $remaining_balance = $purchaser->balance->subtract($total_price);
        if ($remaining_balance->isNegative()) {
            $this->_result = self::RESULT_NOT_ENOUGH_BALANCE;
            $this->_message = "Not enough balance. {$purchaser->full_name} only has {$purchaser->balance}. Tried to spend {$total_price}.";
            return;
        }

        // Loop categories within this transaction
        foreach ($transaction_products->map(fn (TransactionProduct $product) => $product->category_id)->unique() as $category_id) {
            $limit_info = UserLimitsHelper::getInfo($purchaser, $category_id);
            /** @var Money $category_limit */
            $category_limit = $limit_info->limit_per;

            // Skip this category if they have unlimited. Saves time querying
            if ($category_limit->equals(Money::parse(-1_00))) {
                continue;
            }

            $category_spent = Money::parse(0);
            $category_spent_orig = UserLimitsHelper::findSpent($purchaser, $category_id, $limit_info);

            // Loop all products in this transaction. If the product's category is the current one in the above loop, add its price to category spent
            foreach ($transaction_products->filter(fn (TransactionProduct $product) => $product->category_id === $category_id) as $product) {
                $category_spent = $category_spent->add(TaxHelper::calculateFor($product->price, $product->quantity, $product->pst !== null));
            }

            // Break loop if we exceed their limit
            if (!UserLimitsHelper::canSpend($purchaser, $category_spent, $category_id, $limit_info)) {
                $this->_result = self::RESULT_NOT_ENOUGH_CATEGORY_BALANCE;
                $this->_message = 'Not enough balance in the ' . Category::find($category_id)->name . ' category. (Limit: ' . $category_limit . ', Remaining: ' . $category_limit->subtract($category_spent_orig) . '). Tried to spend ' . $category_spent;
                return;
            }
        }

        $transaction_products->each(fn (TransactionProduct $product) => $product->product->removeStock(
            $order_products->firstWhere('id', $product->product_id)->quantity
        ));

        $transaction = new Transaction();
        $transaction->purchaser_id = $purchaser->id;
        $transaction->cashier_id = auth()->id();
        $transaction->total_price = $total_price;

        if ($request->has('rotation_id')) {
            // For seeding random rotations
            $transaction->rotation_id = $request->get('rotation_id');
        } else {
            // TODO: cannot make order without current rotation
            $transaction->rotation_id = resolve(RotationHelper::class)->getCurrentRotation()->id;
        }
        if ($request->has('created_at')) {
            // For seeding random times
            $transaction->created_at = $request->created_at;
        }
        $transaction->save();

        $transaction_products->each(function (TransactionProduct $product) use ($transaction) {
            $product->transaction_id = $transaction->id;
            $product->save();
        });

        $purchaser->update(['balance' => $remaining_balance]);

        $this->_result = self::RESULT_SUCCESS;
        $this->_message = 'Order #' . $transaction->id . '. ' . $purchaser->full_name . ' now has ' . $remaining_balance;
        $this->_transaction = $transaction->refresh();
    }

    public function redirect(): RedirectResponse
    {
        return match ($this->getResult()) {
            self::RESULT_NO_SELF_PURCHASE => redirect('/')->with('error', $this->getMessage()),
            self::RESULT_SUCCESS => redirect('/')->with([
                'success' => $this->getMessage(),
                'last_purchaser_id' => $this->_transaction->purchaser_id,
            ]),
            default => redirect()->back()->with('error', $this->getMessage()),
        };
    }
}
