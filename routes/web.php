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
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\HasPermission;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\StatisticsPageController;

Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::post('/login/auth', [LoginController::class, 'auth'])->name('login_auth');

// Middleware('auth') requires the user to be signed in to view the page
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        if (hasPermission('cashier')) {
            return view('pages.index', ['users' => User::query()->unless(hasPermission('cashier_self_purchases'), function ($query) {
                $query->where('users.id', '!=', auth()->id());
            })->select(['id', 'full_name', 'balance'])->get()]);
        } else {
            // TODO: figure out what to do with users who dont have permission. when they sign in they get a 403 page, not nice UX
            return view('pages.403');
        }
    })->name('index');
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

    // Check their role can access the page
    Route::middleware(HasPermission::class)->group(function () {

        /*
         * Cashier
         */
        Route::group(['permission' => 'cashier'], function () {
            Route::get('/orders/{id}', [TransactionController::class, 'order'])->name('orders_new');
            Route::post('/orders/submit', [TransactionController::class, 'submit'])->name('orders_new_form');
        });

        /*
         * Orders
         */
        Route::group(['permission' => 'orders'], function () {
            Route::group(['permission' => 'orders_list'], function () {
                Route::get('/orders', [TransactionController::class, 'list'])->name('orders_list');
            });

            Route::group(['permission' => 'orders_view'], function () {
                Route::get('/orders/view/{id}', [TransactionController::class, 'view'])->name('orders_view');
            });

            Route::group(['permission' => 'orders_return'], function () {
                Route::get('/orders/return/order/{id}', [TransactionController::class, 'returnTransaction'])->name('orders_return');
                Route::get('/orders/return/item/{item}/{order}', [TransactionController::class, 'returnItem'])->name('orders_return_item');
            });
        });

        /*
         * Users
         */
        Route::group(['permission' => 'users'], function () {
            Route::group(['permission' => 'users_list'], function () {
                Route::get('/users', [UserController::class, 'list'])->name('users_list');
            });

            Route::group(['permission' => 'users_view'], function () {
                Route::get('/users/view/{id}', [UserController::class, 'view'])->name('users_view');
            });

            Route::group(['permission' => 'users_manage'], function () {
                Route::get('/users/new', [UserController::class, 'form'])->name('users_new');
                Route::post('/users/new', [UserController::class, 'new'])->name('users_new_form');

                Route::get('/users/edit/{id}', [UserController::class, 'form'])->name('users_edit');
                Route::post('/users/edit', [UserController::class, 'edit'])->name('users_edit_form');

                Route::get('/users/delete/{id}', [UserController::class, 'delete'])->name('users_delete');
            });
        });

        /*
         * Products
         */
        Route::group(['permission' => 'products'], function () {
            Route::group(['permission' => 'products_list'], function () {
                Route::get('/products', [ProductController::class, 'list'])->name('products_list');
            });

            Route::group(['permission' => 'products_manage'], function () {
                Route::get('/products/new', [ProductController::class, 'form'])->name('products_new');
                Route::post('/products/new', [ProductController::class, 'new']);

                Route::get('/products/edit/{id}', [ProductController::class, 'form'])->name('products_edit');
                Route::post('/products/edit', [ProductController::class, 'edit'])->name('products_edit_form');

                Route::get('/products/delete/{id}', [ProductController::class, 'delete'])->name('products_delete');
            });

            Route::group(['permission' => 'products_adjust'], function () {
                Route::get('/products/adjust', [ProductController::class, 'adjustList'])->name('products_adjust');
                Route::post('/products/adjust/ajax', [ProductController::class, 'ajaxInit'])->name('products_adjust_ajax');
                Route::post('/products/adjust', [ProductController::class, 'adjustStock'])->name('products_adjust_form');
            });
        });

        /*
         * Activities
         */
        Route::group(['permission' => 'activities'], function () {
            Route::group(['permission' => 'activities_list'], function () {
                Route::get('/activities', [ActivityController::class, 'list'])->name('activities_list');
            });

            Route::group(['permission' => 'activities_view'], function () {
                Route::get('/activities/view/{id}', [ActivityController::class, 'view'])->name('activities_view');
            });

            Route::group(['permission' => 'activities_manage'], function () {
                Route::get('/activities/new/{date?}', [ActivityController::class, 'form'])->name('activities_new');
                Route::post('/activities/new', [ActivityController::class, 'new'])->name('activities_new_form');

                Route::get('/activities/edit/{id}', [ActivityController::class, 'form'])->name('activities_edit');
                Route::post('/activities/edit', [ActivityController::class, 'edit'])->name('activities_edit_form');

                Route::post('/activities/view/search', [ActivityController::class, 'ajaxInit'])->name('activities_user_search');
                Route::get('/activities/view/{id}/add/{user}', [ActivityController::class, 'registerUser'])->name('activities_user_add');

                Route::get('/activities/delete/{id}', [ActivityController::class, 'delete'])->name('activities_delete');
            });
        });

        /*
         * Statistics
         */
        Route::group(['permission' => 'statistics'], function () {
            Route::get('/statistics', [StatisticsPageController::class, 'view'])->name('statistics');
            Route::post('/statistics', [SettingsController::class, 'editStatsTime']);
        });

        /*
         * Settings
         */
        Route::group(['permission' => 'settings'], function () {
            Route::get('/settings', [SettingsController::class, 'view'])->name('settings');

            Route::group(['permission' => 'settings_general'], function () {
                Route::post('/settings', [SettingsController::class, 'editSettings'])->name('settings_form');
            });

            Route::group(['permission' => 'settings_roles_manage'], function () {
                Route::get('/settings/roles/new', [RoleController::class, 'form'])->name('settings_roles_new');
                Route::post('/settings/roles/new', [RoleController::class, 'new'])->name('settings_roles_new_form');

                Route::get('/settings/roles/edit/{id}', [RoleController::class, 'form'])->name('settings_roles_edit');
                Route::post('/settings/roles/edit', [RoleController::class, 'edit'])->name('settings_roles_edit_form');

                Route::get('/settings/roles/order', [RoleController::class, 'order'])->name('settings_roles_order_ajax');

                Route::get('/settings/roles/delete/{id}', [RoleController::class, 'delete'])->name('settings_roles_delete');
            });

            /*
             * Categories
             */
            Route::group(['permission' => 'settings_categories_manage'], function () {
                Route::get('/settings/categories/new', [CategoryController::class, 'form'])->name('settings_categories_new');
                Route::post('/settings/categories/new', [CategoryController::class, 'new'])->name('settings_categories_new_form');

                Route::get('/settings/categories/edit/{id}', [CategoryController::class, 'form'])->name('settings_categories_edit');
                Route::post('/settings/categories/edit', [CategoryController::class, 'edit'])->name('settings_categories_edit_form');

                Route::get('/settings/categories/delete/{id}', [CategoryController::class, 'delete'])->name('settings_categories_delete');
            });
        });
    });
});
