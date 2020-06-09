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
use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return view('pages.login');
})->name('login');
Route::post('/login/auth', 'LoginController@auth');

// Middleware('auth') requires the user to be signed in to view the page
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('pages.index');
    });
    Route::get('/logout', 'LoginController@logout');

    /* Cashier */
    Route::get('/orders', function () {
        return view('pages.orders.list');
    });
    Route::get('/orders/view/{id}', function () {
        return view('pages.orders.view');
    })->where('id', '[0-9]+');
    Route::get('/orders/{id}', function () {
        return view('pages.orders.order');
    })->where('id', '[0-9]+');
    Route::post('/orders/{id}/submit', 'OrderController@submit')->where('id', '[0-9]+');

    // Admin Only
    Route::middleware(CheckRole::class)->group(function () {

        /* Orders */
        Route::get('/orders/return/order/{id}', 'OrderController@returnOrder')->where('id', '[0-9]+')->name('return_order');
        Route::get('/orders/return/item/{item}/{order}', 'OrderController@returnItem')->where(['id', '[0-9]+'], ['order', '[0-9]+'])->name('return_item');

        /* Users */
        Route::get('/users', function () {
            return view('pages.users.list');
        });
        Route::get('/users/new', function () {
            return view('pages.users.form');
        });
        Route::post('/users/new/commit', 'UsersController@new');
        Route::get('/users/edit/{id}', function () {
            return view('pages.users.form');
        })->where('id', '[0-9]+');
        Route::post('/users/edit/commit', 'UsersController@edit')->where('id', '[0-9]+');
        Route::get('/users/info/{id}', function () {
            return view('pages.users.info');
        })->where('id', '[0-9]+');
        Route::get('/users/delete/{id}', 'UsersController@delete')->where('id', '[0-9]+')->name('delete_user');

        /* Products */
        Route::get('/products', function () {
            return view('pages.products.list');
        });
        Route::get('/products/new', function () {
            return view('pages.products.form');
        });
        Route::post('/products/new/commit', 'ProductsController@new');
        Route::get('/products/edit/{id}', function () {
            return view('pages.products.form');
        })->where('id', '[0-9]+');
        Route::post('/products/edit/commit', 'ProductsController@edit')->where('id', '[0-9]+');
        Route::get('/products/delete/{id}', 'ProductsController@delete')->where('id', '[0-9]+')->name('delete_product');
        Route::get('/products/adjust', function () {
            return view('pages.products.adjust.list');
        })->where('id', '[0-9]+');
        Route::post('/products/adjust/ajax', 'ProductsController@ajaxInit')->name('adjustAjax');
        Route::post('/products/adjust/commit', 'ProductsController@adjustStock');

        /* Statistics */
        Route::get('/statistics/graphs', function () {
            return view('pages.statistics.graphs');
        });
        Route::post('/statistics/graphs/update', 'SettingsController@editLookBack');

        /* Settings */
        Route::get('/settings', function () {
            return view('pages.settings.settings');
        });
        Route::post('/settings/submit', 'SettingsController@editTax');
        Route::get('/settings/category/new', function () {
            return view('pages.settings.category.new');
        });
        Route::post('/settings/category/new', 'SettingsController@newCat');
        Route::get('/settings/category/delete/{name}', 'SettingsController@deleteCat')->where('id', '[0-9]+')->name('delete_category');
    });
});
