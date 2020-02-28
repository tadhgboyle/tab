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

use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return View::make('pages.login');
})->name('login');
Route::post('/login/auth', 'LoginController@auth');
Route::get('/users/new', function () {
    return View::make('pages.users.new');
});
Route::post('/users/new/commit', 'UsersController@new');
// middleware('auth') requires the user to be signed in to view the page
Route::middleware('auth')->group(function () {
    Route::match(['post', 'get'], '/', function () {
        return View::make('pages.index');
    });
    // cashier
    Route::get('/cashier/order/{id}', function () {
        return View::make('pages.cashier.order');
    })->where('id', '[0-9]+');
    Route::post('/cashier/order/{id}/submit', 'CashierController@submit');
    Route::get('/cashier/orders', function () {
        return View::make('pages.cashier.orders');
    });
    // users
    Route::get('/users', function () {
        return View::make('pages.users.list');
    });
    Route::get('/users/edit/{id}', function () {
        return View::make('pages.users.edit');
    })->where('id', '[0-9]+');
    Route::post('/users/edit/{id}/commit', 'UsersController@edit');
    Route::get('/users/delete/{id}', 'UsersController@delete')->where('id', '[0-9]+');
    // products
    Route::get('/products', function () {
        return View::make('pages.products.list');
    });
    Route::get('/products/new', function () {
        return View::make('pages.products.new');
    });
    Route::post('/products/new/commit', 'ProductsController@new');
    Route::get('/products/edit/{id}', function () {
        return View::make('pages.products.edit');
    })->where('id', '[0-9]+');
    Route::post('/products/edit/{id}/commit', 'ProductsController@edit');
    Route::get('/products/delete/{id}', 'ProductsController@delete')->where('id', '[0-9]+');
    Route::get('/logout', 'LoginController@logout')->name('logout');
});
