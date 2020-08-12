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

use App\Http\Middleware\CheckRole;
use App\User;
use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return view('pages.login');
})->name('login');
Route::post('/login/auth', 'LoginController@auth');

// Middleware('auth') requires the user to be signed in to view the page
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('pages.index');
    })->name('index');
    Route::get('/logout', 'LoginController@logout')->name('logout');

    // Check their role can access the page
    Route::middleware(CheckRole::class)->group(function () {

        /* Cashier */
        Route::get('/orders/{id}', function () {
            return view('pages.orders.order');
        })->where('id', '[0-9]+');

        /* Orders */
        Route::get('/orders', function () {
            return view('pages.orders.list');
        })->name('orders');
        Route::get('/orders/view/{id}', function () {
            return view('pages.orders.view');
        })->where('id', '[0-9]+');
        Route::post('/orders/submit', 'OrderController@submit');
        /* Orders */
        Route::get('/orders/return/order/{id}', 'OrderController@returnOrder')->where('id', '[0-9]+')->name('return_order');
        Route::get('/orders/return/item/{item}/{order}', 'OrderController@returnItem')->where(['id', '[0-9]+'], ['order', '[0-9]+'])->name('return_item');

        /* Users */
        Route::get('/users', function () {
            return view('pages.users.list');
        })->name('users');
        Route::get('/users/new', function () {
            return view('pages.users.form');
        })->name('users_new');
        Route::post('/users/new', 'UsersController@new');
        Route::get('/users/edit/{id}', function () {
            return view('pages.users.form');
        })->where('id', '[0-9]+');
        Route::post('/users/edit', 'UsersController@edit');
        Route::get('/users/info/{id}', function () {
            return view('pages.users.info');
        })->where('id', '[0-9]+');
        Route::get('/users/delete/{id}', 'UsersController@delete')->where('id', '[0-9]+')->name('delete_user');

        /* Products */
        Route::get('/products', function () {
            return view('pages.products.list');
        })->name('products');
        Route::get('/products/new', function () {
            return view('pages.products.form');
        })->name('products_new');
        Route::post('/products/new', 'ProductsController@new');
        Route::get('/products/edit/{id}', function () {
            return view('pages.products.form');
        })->where('id', '[0-9]+');
        Route::post('/products/edit', 'ProductsController@edit');
        Route::get('/products/delete/{id}', 'ProductsController@delete')->where('id', '[0-9]+')->name('delete_product');
        Route::get('/products/adjust', function () {
            return view('pages.products.adjust.list');
        })->where('id', '[0-9]+')->name('products_adjust');
        Route::post('/products/adjust/ajax', 'ProductsController@ajaxInit')->name('adjust_ajax');
        Route::post('/products/adjust', 'ProductsController@adjustStock');

        /* Statistics */
        Route::get('/statistics', function () {
            return view('pages.statistics.statistics');
        })->name('statistics');
        Route::post('/statistics', 'SettingsController@editStatsTime');

        /* Settings */
        Route::get('/settings', function () {
            return view('pages.settings.settings');
        })->name('settings');
        Route::post('/settings', 'SettingsController@editSettings');
        Route::get('/settings/category/new', function () {
            return view('pages.settings.category.new');
        });
        Route::post('/settings/category/new', 'SettingsController@newCat');
        Route::get('/settings/category/delete/{name}', 'SettingsController@deleteCat')->name('delete_category');
    });
});
