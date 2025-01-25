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

use App\Helpers\Permission;
use App\Http\Controllers\Admin\PurchaseOrdersController;
use App\Http\Controllers\SuppliersController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Middleware\RequiresOwnFamily;
use App\Http\Middleware\RequiresFamilyAdmin;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\User\FamilyController;
use App\Http\Controllers\User\PayoutController;
use App\Http\Controllers\Admin\CashierController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\GiftCardController;
use App\Http\Controllers\Admin\RotationController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Middleware\RequiresFamilyAdminOrSelf;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\User\FamilyMemberController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\GiftCardAssignmentController;
use App\Http\Controllers\Admin\ProductVariantOptionController;
use App\Http\Controllers\Admin\ActivityRegistrationsController;
use App\Http\Controllers\Admin\ProductVariantOptionValueController;
use App\Http\Controllers\Admin\FamilyController as AdminFamilyController;
use App\Http\Controllers\Admin\FamilyMemberController as AdminFamilyMemberController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'login'])->name('login');
    Route::post('/login', [LoginController::class, 'auth'])->name('login_auth');
});

Route::middleware('auth')->group(function () {
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/', static function () {
        $staffRoute = hasPermission(Permission::DASHBOARD)
            ? 'dashboard'
            : 'cashier';

        if (auth()->user()->role->staff) {
            return redirect()->route($staffRoute);
        }

        if (auth()->user()->family) {
            return redirect()->route('family_view', auth()->user()->family);
        }

        // TODO: some other page
        return abort(404);
    });

    /*
     * Family
     */
    Route::group(['middleware' => RequiresOwnFamily::class, 'prefix' => '/families/{family}'], static function () {
        Route::get('/', [FamilyController::class, 'show'])->name('family_view');

        Route::group(['middleware' => RequiresFamilyAdmin::class], static function () {
            Route::get('/pdf', [FamilyController::class, 'downloadPdf'])->name('family_pdf');
        });

        Route::group(['prefix' => '/members/{familyMember}'], static function () {
            Route::group(['middleware' => RequiresFamilyAdminOrSelf::class], static function () {
                Route::get('/', [FamilyMemberController::class, 'show'])->name('families_member_view');
                Route::get('/pdf', [FamilyMemberController::class, 'downloadPdf'])->name('family_member_pdf');
                Route::get('/payout', [PayoutController::class, 'create'])->name('family_member_payout');
                Route::post('/payout', [PayoutController::class, 'store'])->name('family_member_payout_store');
                Route::get('/payout/success', [PayoutController::class, 'stripeSuccessCallback'])->name('family_member_payout_success');
                Route::get('/payout/cancel', [PayoutController::class, 'stripeCancelCallback'])->name('family_member_payout_cancel');
            });

            Route::group(['middleware' => RequiresFamilyAdmin::class], static function () {
                Route::get('/edit', [FamilyMemberController::class, 'edit'])->name('families_member_edit');
                Route::put('/edit', [FamilyMemberController::class, 'update'])->name('families_member_update');
            });
        });
    });

    Route::prefix('/admin')->group(static function () {
        /*
        * Dashboard
        */
        Route::group(['middleware' => 'permission:' . Permission::DASHBOARD], static function () {
            Route::get('/dashboard', DashboardController::class)->name('dashboard');
        });

        /*
        * Cashier
        */
        Route::group(['middleware' => 'permission:' . Permission::CASHIER_CREATE], static function () {
            Route::get('/cashier', CashierController::class)->name('cashier');

            Route::get('/orders/create/{user}', [OrderController::class, 'create'])->name('orders_create');
            Route::post('/orders/create/{user}', [OrderController::class, 'store'])->name('orders_store');

            // Get product metadata via JS fetch
            Route::get('/products/{product}/info', [ProductController::class, 'ajaxGetInfo'])->name('products_show');

            // Check user limit via JS fetch
            Route::get('/users/{user}/check-limit/{category}', [UserController::class, 'ajaxCheckLimit'])->name('users_check_limit');

            // Get gift card validity via JS fetch
            Route::get('/gift-cards/check-validity', [GiftCardController::class, 'ajaxCheckValidity'])->name('gift_cards_check_validity');
        });

        /*
        * Orders
        */
        Route::group(['middleware' => 'permission:' . Permission::ORDERS, 'prefix' => '/orders'], static function () {
            Route::group(['middleware' => 'permission:' . Permission::ORDERS_LIST], static function () {
                Route::get('/', [OrderController::class, 'index'])->name('orders_list');
            });

            Route::group(['middleware' => 'permission:' . Permission::ORDERS_VIEW], static function () {
                Route::get('/{order}', [OrderController::class, 'show'])->name('orders_view');
            });

            Route::group(['middleware' => 'permission:' . Permission::ORDERS_RETURN], static function () {
                Route::put('/{order}/return', [OrderController::class, 'returnOrder'])->name('orders_return');
                Route::put('/{order}/return/{orderProduct}', [OrderController::class, 'returnProduct'])->name('order_return_product');
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
            });
        });

        /*
        * Families
        */
        Route::group(['middleware' => 'permission:' . Permission::FAMILIES, 'prefix' => '/families'], static function () {
            Route::group(['middleware' => 'permission:' . Permission::FAMILIES_LIST], static function () {
                Route::get('/', [AdminFamilyController::class, 'index'])->name('families_list');
            });

            Route::group(['middleware' => 'permission:' . Permission::FAMILIES_VIEW], static function () {
                Route::get('/{family}', [AdminFamilyController::class, 'show'])->whereNumber('family')->name('families_view');
                Route::get('/{family}/pdf', [AdminFamilyController::class, 'downloadPdf'])->name('families_pdf');
            });

            Route::group(['middleware' => 'permission:' . Permission::FAMILIES_MANAGE], static function () {
                Route::get('/create', [AdminFamilyController::class, 'create'])->name('families_create');
                Route::post('/create', [AdminFamilyController::class, 'store'])->name('families_store');
                Route::get('/{family}/edit', [AdminFamilyController::class, 'edit'])->name('families_edit');
                Route::put('/{family}/edit', [AdminFamilyController::class, 'update'])->name('families_update');

                Route::get('/{family}/search', [AdminFamilyMemberController::class, 'ajaxUserSearch'])->name('families_user_search');
                Route::get('/{family}/add/{user}', [AdminFamilyMemberController::class, 'store'])->name('families_user_add');
                Route::patch('/{family}/edit/{familyMember}', [AdminFamilyMemberController::class, 'update'])->name('families_user_update');
                Route::delete('/{family}/remove/{familyMember}', [AdminFamilyMemberController::class, 'delete'])->name('families_user_remove');
            });
        });

        /*
        * Products
        */
        Route::group(['middleware' => 'permission:' . Permission::PRODUCTS, 'prefix' => '/products'], static function () {
            Route::group(['middleware' => 'permission:' . Permission::PRODUCTS_LIST], static function () {
                Route::get('/', [ProductController::class, 'index'])->name('products_list');
            });

            Route::group(['middleware' => 'permission:' . Permission::PRODUCTS_VIEW], static function () {
                Route::get('/{product}', [ProductController::class, 'show'])->whereNumber('product')->name('products_view');
            });

            Route::group(['middleware' => 'permission:' . Permission::PRODUCTS_MANAGE], static function () {
                Route::get('/create', [ProductController::class, 'create'])->name('products_create');
                Route::post('/create', [ProductController::class, 'store'])->name('products_store');
                Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('products_edit');
                Route::put('/{product}/edit', [ProductController::class, 'update'])->name('products_update');
                Route::delete('/{product}', [ProductController::class, 'delete'])->name('products_delete');

                Route::get('/{product}/variant/create', [ProductVariantController::class, 'create'])->name('products_variants_create');
                Route::post('/{product}/variant/create', [ProductVariantController::class, 'store'])->name('products_variants_store');
                // TODO: scopeBindings()
                Route::get('/{product}/variant/{productVariant}/edit', [ProductVariantController::class, 'edit'])->name('products_variants_edit');
                Route::put('/{product}/variant/{productVariant}/edit', [ProductVariantController::class, 'update'])->name('products_variants_update');
                Route::delete('/{product}/variant/{productVariant}', [ProductVariantController::class, 'destroy'])->name('products_variants_delete');

                Route::get('/{product}/variant-options/create', [ProductVariantOptionController::class, 'create'])->name('products_variant-options_create');
                Route::post('/{product}/variant-options/create', [ProductVariantOptionController::class, 'store'])->name('products_variant-options_store');
                Route::get('/{product}/variant-options/{productVariantOption}/edit', [ProductVariantOptionController::class, 'edit'])->name('products_variant-options_edit');
                Route::put('/{product}/variant-options/{productVariantOption}/edit', [ProductVariantOptionController::class, 'update'])->name('products_variant-options_update');
                Route::delete('/{product}/variant-options/{productVariantOption}', [ProductVariantOptionController::class, 'destroy'])->name('products_variant-options_delete');

                Route::post('/{product}/variant-options/{productVariantOption}/values/create', [ProductVariantOptionValueController::class, 'store'])->name('products_variant-options_values_store');
                Route::put('/{product}/variant-options/{productVariantOption}/values/{productVariantOptionValue}', [ProductVariantOptionValueController::class, 'update'])->name('products_variant-options_values_update');
                Route::delete('/{product}/variant-options/{productVariantOption}/values/{productVariantOptionValue}', [ProductVariantOptionValueController::class, 'destroy'])->name('products_variant-options_values_delete');
            });

            Route::group(['middleware' => 'permission:' . Permission::PRODUCTS_LEDGER], static function () {
                Route::get('/ledger', [ProductController::class, 'adjustList'])->name('products_ledger');
                Route::get('/ledger/{product}', [ProductController::class, 'ajaxGetPage'])->name('products_ledger_ajax');
                Route::patch('/ledger/{product}/{productVariant?}', [ProductController::class, 'adjustStock'])->name('products_ledger_form');
            });
        });

        /*
        * Purchase Orders
        */
        Route::group(['middleware' => 'permission:' . Permission::PURCHASE_ORDERS, 'prefix' => '/purchase-orders'], static function () {
            Route::group(['middleware' => 'permission:' . Permission::PURCHASE_ORDERS_LIST], static function () {
                Route::get('/', [PurchaseOrdersController::class, 'index'])->name('purchase_orders_list');
            });

            Route::group(['middleware' => 'permission:' . Permission::PURCHASE_ORDERS_VIEW], static function () {
                Route::get('/{purchaseOrder}', [PurchaseOrdersController::class, 'show'])->whereNumber('purchaseOrder')->name('purchase_orders_view');
            });

            Route::group(['middleware' => 'permission:' . Permission::PURCHASE_ORDERS_MANAGE], static function () {
                Route::get('/create', [PurchaseOrdersController::class, 'create'])->name('purchase_orders_create');
                Route::post('/create', [PurchaseOrdersController::class, 'store'])->name('purchase_orders_store');

                Route::get('/{purchaseOrder}/edit', [PurchaseOrdersController::class, 'edit'])->name('purchase_orders_edit');
                Route::put('/{purchaseOrder}/edit', [PurchaseOrdersController::class, 'update'])->name('purchase_orders_update');
                Route::put('/{purchaseOrder}/cancel', [PurchaseOrdersController::class, 'cancel'])->name('purchase_orders_cancel');
            });
        });

        /*
        * Activities
        */
        Route::group(['middleware' => 'permission:' . Permission::ACTIVITIES, 'prefix' => '/activities'], static function () {
            Route::group(['middleware' => 'permission:' . Permission::ACTIVITIES_LIST], static function () {
                Route::get('/', [ActivityController::class, 'calendar'])->name('activities_calendar');
                Route::get('/list', [ActivityController::class, 'index'])->name('activities_list');
            });

            Route::group(['middleware' => 'permission:' . Permission::ACTIVITIES_VIEW], static function () {
                Route::get('/{activity}', [ActivityController::class, 'show'])->whereNumber('activity')->name('activities_view');
            });

            Route::group(['middleware' => 'permission:' . Permission::ACTIVITIES_MANAGE], static function () {
                Route::get('/create/{date?}', [ActivityController::class, 'create'])->name('activities_create');
                Route::post('/create', [ActivityController::class, 'store'])->name('activities_store');
                Route::get('/{activity}/edit', [ActivityController::class, 'edit'])->name('activities_edit');
                Route::put('/{activity}/edit', [ActivityController::class, 'update'])->name('activities_update');
                Route::delete('/{activity}', [ActivityController::class, 'delete'])->name('activities_delete');
            });

            Route::group(['middleware' => 'permission:' . Permission::ACTIVITIES_MANAGE_REGISTRATIONS], static function () {
                Route::get('/{activity}/search', [ActivityRegistrationsController::class, 'ajaxUserSearch'])->name('activities_user_search');
                // TODO: make POST
                Route::get('/{activity}/register/{user}', [ActivityRegistrationsController::class, 'store'])->name('activities_register_user');
                Route::delete('/{activity}/remove/{activityRegistration}', [ActivityRegistrationsController::class, 'delete'])->name('activities_remove_user');
            });
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

                // TODO: make POST
                Route::get('/{giftCard}/assign/{user}', [GiftCardAssignmentController::class, 'store'])->name('settings_gift-cards_assign');
                Route::delete('/{giftCard}/unassign/{user}', [GiftCardAssignmentController::class, 'destroy'])->name('settings_gift-cards_unassign');

                Route::get('/{giftCard}/search', [GiftCardAssignmentController::class, 'ajaxUserSearch'])->name('settings_gift-cards_assign_search');
            });
        });
    });

    Route::impersonate();
});
