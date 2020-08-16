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

use App\Http\Middleware\CheckPermission;
use App\Roles;
use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return view('pages.login');
})->name('login');
Route::post('/login/auth', 'LoginController@auth');

// Middleware('auth') requires the user to be signed in to view the page
Route::middleware('auth')->group(function () {

    Route::get('/', function () {
        if (Roles::hasPermission(Auth::user()->role, 'cashier')) return view('pages.index');
        else return view('pages.403');
    })->name('index');
    Route::get('/logout', 'LoginController@logout')->name('logout');

    // Check their role can access the page
    Route::middleware(CheckPermission::class)->group(function () {

        /* 
         * Cashier 
         */
        Route::group(['permission' => 'cashier'], function() {
            Route::get('/orders/{id}', function () {
                return view('pages.orders.order');
            })->where('id', '[0-9]+');
            
            Route::post('/orders/submit', 'OrderController@submit');
        });

        /* 
         * Orders 
         */
        Route::group(['permission' => 'orders'], function () {
            Route::group(['permission' => 'orders_list'], function() {
                Route::get('/orders', function () {
                    return view('pages.orders.list');
                })->name('orders_list');
            });

            Route::group(['permission' => 'orders_view'], function() {
                Route::get('/orders/view/{id}', function () {
                    return view('pages.orders.view');
                })->where('id', '[0-9]+')->name('orders_view');
            });

            Route::group(['permission' => 'orders_return'], function() {
                Route::get('/orders/return/order/{id}', 'OrderController@returnOrder')->where('id', '[0-9]+')->name('orders_return');
                Route::get('/orders/return/item/{item}/{order}', 'OrderController@returnItem')->where(['id', '[0-9]+'], ['order', '[0-9]+'])->name('orders_return_item');
            });
            });

        /* 
         * Users 
         */
        Route::group(['permission' => 'users'], function () {
            Route::group(['permission' => 'users_list'], function () {
                Route::get('/users', function () {
                    return view('pages.users.list');
                })->name('users_list');
            });

            Route::group(['permission' => 'users_view'], function () {
                Route::get('/users/view/{id}', function () {
                    return view('pages.users.view');
                })->where('id', '[0-9]+')->name('users_view');
            });

            Route::group(['permission' => 'users_manage'], function () {
                Route::get('/users/new', function () {
                    return view('pages.users.form');
                })->name('users_new');
                Route::post('/users/new', 'UsersController@new')->name('users_new_form');

                Route::get('/users/edit/{id}', function () {
                    return view('pages.users.form');
                })->where('id', '[0-9]+')->name('users_edit');
                Route::post('/users/edit', 'UsersController@edit')->name('users_edit_form');

                Route::get('/users/delete/{id}', 'UsersController@delete')->where('id', '[0-9]+')->name('users_delete');
            });
        });

        /* 
         * Products 
         */
        Route::group(['permission' => 'products'], function() {
            Route::group(['permission' => 'products_list'], function () {
                Route::get('/products', function () {
                    return view('pages.products.list');
                })->name('products_list');
            });

            Route::group(['permission' => 'products_manage'], function () {
                Route::get('/products/new', function () {
                    return view('pages.products.form');
                })->name('products_new');
                Route::post('/products/new', 'ProductsController@new');

                Route::get('/products/edit/{id}', function () {
                    return view('pages.products.form');
                })->where('id', '[0-9]+')->name('products_edit');
                Route::post('/products/edit', 'ProductsController@edit')->name('products_edit_form');

                Route::get('/products/delete/{id}', 'ProductsController@delete')->where('id', '[0-9]+')->name('products_delete');
            });

            Route::group(['permission' => 'products_adjust'], function () {
                Route::get('/products/adjust', function () {
                    return view('pages.products.adjust.list');
                })->where('id', '[0-9]+')->name('products_adjust');
                Route::post('/products/adjust/ajax', 'ProductsController@ajaxInit')->name('products_adjust_ajax');
                Route::post('/products/adjust', 'ProductsController@adjustStock')->name('products_adjust_form');
            });
        });

        /* 
         * Statistics 
         */
        Route::group(['permission' => 'statistics'], function() {
            Route::get('/statistics', function () {
                return view('pages.statistics.statistics');
            })->name('statistics');

            Route::post('/statistics', 'SettingsController@editStatsTime');
        });

        /* 
         * Settings 
         */
        Route::group(['permission' => 'settings'], function() {
            Route::get('/settings', function () {
                return view('pages.settings.settings');
            })->name('settings');

            Route::group(['permission' => 'settings_general'], function () {
                Route::post('/settings', 'SettingsController@editSettings')->name('settings_form');
            });

            Route::group(['permission' => 'settings_roles_manage'], function () {
                Route::get('/settings/roles/new', function () {
                    return view('pages.settings.roles.form');
                })->name('settings_roles_new');
                Route::post('/settings/roles/new',
                    'RolesController@new'
                )->name('settings_roles_new_form');

                Route::get('/settings/roles/edit/{id}', function () {
                    return view('pages.settings.roles.form');
                })->where('id', '[0-9]+')->name('settings_roles_edit');
                Route::post('/settings/roles/edit',
                    'RolesController@edit'
                )->name('settings_roles_edit_form');

                Route::get('/settings/roles/delete/{id}', 'RolesController@delete')->name('settings_roles_delete');

            });

            Route::group(['permission' => 'settings_categories_manage'], function () {
                Route::get('/settings/categories/new', function () {
                    return view('pages.settings.categories.form');
                })->name('settings_category_new');
                Route::post('/settings/categories/new',
                    'SettingsController@newCat'
                )->name('settings_categories_new_form');

                Route::get('/settings/categories/delete/{name}', 'SettingsController@deleteCat')->name('settings_categories_delete');
            });
        });
    });
});
