<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Models\User;
use App\Helpers\Permission;
use App\Helpers\RotationHelper;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GiftCardController;
use App\Http\Controllers\RotationController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\TransactionController;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'login'])->name('login');
    Route::post('/login', [LoginController::class, 'auth'])->name('login_auth');
});

Route::middleware('auth')->group(function () {
    Route::get('/', static function () {
        if (!hasPermission(Permission::CASHIER) || !hasPermission(Permission::CASHIER_CREATE)) {
            // TODO: figure out what to do with users who dont have permission. when they sign in they get a 403 page, not nice UX
            return view('pages.403');
        }

        // TODO: similar handling of rotation selection/invalidity to statistics page
        return view('pages.index', [
            'users' => User::query()
                ->unless(hasPermission(Permission::CASHIER_SELF_PURCHASES), function (EloquentBuilder $query) {
                    $query->where('users.id', '!=', auth()->id());
                })
                ->unless(hasPermission(Permission::CASHIER_USERS_OTHER_ROTATIONS), function (EloquentBuilder $query) {
                    $query->whereHas('rotations', function (EloquentBuilder $query) {
                        return $query->where('rotation_id', resolve(RotationHelper::class)->getCurrentRotation()->id);
                    });
                })
                ->select(['id', 'full_name', 'balance'])
                ->with('rotations')
                ->get(),
            'currentRotation' => resolve(RotationHelper::class)->getCurrentRotation()
        ]);
    })->name('index');

    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

    /*
     * Cashier
     */
    Route::group(['middleware' => 'permission:' . Permission::CASHIER], static function () {
        Route::get('/orders/create/{user}', [TransactionController::class, 'create'])->name('orders_create');
        Route::post('/orders/create/{user}', [TransactionController::class, 'store'])->name('orders_store');

        // Get product metadata via JS fetch
        Route::get('/products/{product}', [ProductController::class, 'ajaxGetInfo'])->whereNumber('product')->name('products_show');

        // Get gift card validity via JS fetch
        Route::get('/gift-cards/check-validity', [GiftCardController::class, 'ajaxCheckValidity'])->name('gift_cards_check_validity');
    });

    /*
     * Orders
     */
    Route::group(['middleware' => 'permission:' . Permission::ORDERS, 'prefix' => '/orders'], static function () {
        Route::group(['middleware' => 'permission:' . Permission::ORDERS_LIST], static function () {
            Route::get('/', [TransactionController::class, 'index'])->name('orders_list');
            Route::get('/{transaction}/products', [TransactionController::class, 'ajaxGetProducts'])->name('orders_products');
        });

        Route::group(['middleware' => 'permission:' . Permission::ORDERS_VIEW], static function () {
            Route::get('/{transaction}', [TransactionController::class, 'show'])->name('orders_view');
        });

        Route::group(['middleware' => 'permission:' . Permission::ORDERS_RETURN], static function () {
            Route::put('/{transaction}/return', [TransactionController::class, 'returnTransaction'])->name('transactions_return');
            Route::put('/{transaction}/return/{transactionProduct}', [TransactionController::class, 'returnProduct'])->name('transaction_return_product');
        });
    });

    /*
     * Users
     */
    Route::group(['middleware' => 'permission:' . Permission::USERS, 'prefix' => '/users'], static function () {
        Route::group(['middleware' => 'permission:' . Permission::USERS_LIST], static function () {
            Route::get('/', [UserController::class, 'index'])->name('users_list');
        });

        Route::group(['middleware' => 'permission:' . Permission::USERS_VIEW], static function () {
            Route::get('/{user}', [UserController::class, 'show'])->whereNumber('user')->name('users_view');
            Route::get('/{user}/pdf', [UserController::class, 'downloadPdf'])->name('users_pdf');
        });

        Route::group(['middleware' => 'permission:' . Permission::USERS_MANAGE], static function () {
            Route::get('/create', [UserController::class, 'create'])->name('users_create');
            Route::post('/create', [UserController::class, 'store'])->name('users_store');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users_edit');
            Route::put('/{user}/edit', [UserController::class, 'update'])->name('users_update');
            Route::delete('/{user}', [UserController::class, 'delete'])->name('users_delete');

            Route::group(['middleware' => 'permission:' . Permission::USERS_PAYOUTS_CREATE], static function () {
                Route::get('/{user}/payout', [PayoutController::class, 'create'])->name('users_payout_create');
                Route::post('/{user}/payout', [PayoutController::class, 'store'])->name('users_payout_store');
            });
        });
    });

    /*
     * Products
     */
    Route::group(['middleware' => 'permission:' . Permission::PRODUCTS, 'prefix' => '/products'], static function () {
        Route::group(['middleware' => 'permission:' . Permission::PRODUCTS_LIST], static function () {
            Route::get('/', [ProductController::class, 'index'])->name('products_list');
        });

        Route::group(['middleware' => 'permission:' . Permission::PRODUCTS_MANAGE], static function () {
            Route::get('/create', [ProductController::class, 'create'])->name('products_create');
            Route::post('/create', [ProductController::class, 'store'])->name('products_store');
            Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('products_edit');
            Route::put('/{product}/edit', [ProductController::class, 'update'])->name('products_update');
            Route::delete('/{product}', [ProductController::class, 'delete'])->name('products_delete');
        });

        Route::group(['middleware' => 'permission:' . Permission::PRODUCTS_LEDGER], static function () {
            Route::get('/ledger', [ProductController::class, 'adjustList'])->name('products_ledger');
            Route::get('/ledger/{product}', [ProductController::class, 'ajaxGetPage'])->name('products_ledger_ajax');
            Route::patch('/ledger/{product}', [ProductController::class, 'adjustStock'])->name('products_ledger_form');
        });
    });

    /*
     * Activities
     */
    Route::group(['middleware' => 'permission:' . Permission::ACTIVITIES, 'prefix' => '/activities'], static function () {
        Route::group(['middleware' => 'permission:' . Permission::ACTIVITIES_LIST], static function () {
            Route::get('/', [ActivityController::class, 'index'])->name('activities_list');
        });

        Route::group(['middleware' => 'permission:' . Permission::ACTIVITIES_VIEW], static function () {
            Route::get('/{activity}', [ActivityController::class, 'show'])->whereNumber('activity')->name('activities_view');
        });

        Route::group(['middleware' => 'permission:' . Permission::ACTIVITIES_MANAGE], static function () {
            Route::get('/create/{date?}', [ActivityController::class, 'create'])->name('activities_create');
            Route::post('/create', [ActivityController::class, 'store'])->name('activities_store');
            Route::get('/{activity}/edit', [ActivityController::class, 'edit'])->name('activities_edit');
            Route::put('/{activity}/edit', [ActivityController::class, 'update'])->name('activities_update');
            Route::post('/{activity}', [ActivityController::class, 'delete'])->name('activities_delete');
        });

        Route::group(['middleware' => 'permission:' . Permission::ACTIVITIES_REGISTER_USER], static function () {
            Route::get('/{activity}/search', [ActivityController::class, 'ajaxUserSearch'])->name('activities_user_search');
            // TODO: make POST
            Route::get('/{activity}/add/{user}', [ActivityController::class, 'registerUser'])->name('activities_user_add');
        });
    });

    /*
     * Statistics
     */
    Route::group(['middleware' => ['permission:' . Permission::STATISTICS]], static function () {
        Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics');
    });

    /*
     * Settings
     */
    Route::group(['middleware' => 'permission:' . Permission::SETTINGS, 'prefix' => '/settings'], static function () {
        Route::get('/', [SettingsController::class, 'view'])->name('settings');

        /*
         * Tax editing
         */
        Route::group(['middleware' => 'permission:' . Permission::SETTINGS_GENERAL], static function () {
            Route::post('/', [SettingsController::class, 'editSettings'])->name('settings_edit');
        });

        /*
         * Roles
         */
        Route::group(['middleware' => 'permission:' . Permission::SETTINGS_ROLES_MANAGE, 'prefix' => '/roles'], static function () {
            Route::get('/create', [RoleController::class, 'create'])->name('settings_roles_create');
            Route::post('/create', [RoleController::class, 'store'])->name('settings_roles_store');
            Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('settings_roles_edit');
            Route::put('/{role}/edit', [RoleController::class, 'update'])->name('settings_roles_update');
            Route::delete('/{role}', [RoleController::class, 'delete'])->name('settings_roles_delete');

            Route::put('/order', [RoleController::class, 'order'])->name('settings_roles_order_ajax');
        });

        /*
         * Rotations
         */
        Route::group(['middleware' => 'permission:' . Permission::SETTINGS_ROTATIONS_MANAGE, 'prefix' => '/rotations'], static function () {
            Route::get('/create', [RotationController::class, 'create'])->name('settings_rotations_create');
            Route::post('/create', [RotationController::class, 'store'])->name('settings_rotations_store');
            Route::get('/{rotation}/edit', [RotationController::class, 'edit'])->name('settings_rotations_edit');
            Route::put('/{rotation}/edit', [RotationController::class, 'update'])->name('settings_rotations_update');
            Route::delete('/{rotation}', [RotationController::class, 'delete'])->name('settings_rotations_delete');
        });

        /*
         * Categories
         */
        Route::group(['middleware' => 'permission:' . Permission::SETTINGS_CATEGORIES_MANAGE, 'prefix' => '/categories'], static function () {
            Route::get('/create', [CategoryController::class, 'create'])->name('settings_categories_create');
            Route::post('/create', [CategoryController::class, 'store'])->name('settings_categories_store');
            Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('settings_categories_edit');
            Route::put('/{category}/edit', [CategoryController::class, 'update'])->name('settings_categories_update');
            Route::delete('/{category}', [CategoryController::class, 'delete'])->name('settings_categories_delete');
        });

        /*
         * Gift cards
         */
        Route::group(['middleware' => 'permission:' . Permission::SETTINGS_GIFT_CARDS_MANAGE, 'prefix' => '/gift-cards'], static function () {
            Route::get('/create', [GiftCardController::class, 'create'])->name('settings_gift-cards_create');
            Route::post('/create', [GiftCardController::class, 'store'])->name('settings_gift-cards_store');
            Route::get('/{giftCard}', [GiftCardController::class, 'show'])->name('settings_gift-cards_view');
        });
    });

    Route::impersonate();
});
