<?php

use Illuminate\Support\Facades\Route;


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

Route::get('/', function () {
    return view('welcome');
});




Route::redirect('/home', '/admin');

Auth::routes(['register' => false]);

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\Admin\HomeController@index')->name('home');

    Route::delete('permissions/destroy', 'App\Http\Controllers\Admin\PermissionsController@massDestroy')->name('permissions.massDestroy');

    Route::resource('permissions', 'App\Http\Controllers\Admin\PermissionsController');

    Route::delete('roles/destroy', 'App\Http\Controllers\Admin\RolesController@massDestroy')->name('roles.massDestroy');

    Route::resource('roles', 'App\Http\Controllers\Admin\RolesController');

    Route::delete('users/destroy', 'App\Http\Controllers\Admin\UsersController@massDestroy')->name('users.massDestroy');

    Route::resource('users', 'App\Http\Controllers\Admin\UsersController');

    Route::delete('order/destroy', 'App\Http\Controllers\Admin\AdminOrderController@massDestroy')->name('order.massDestroy');

    Route::resource('order', 'App\Http\Controllers\Admin\AdminOrderController');

    Route::delete('products/destroy', 'App\Http\Controllers\Admin\ProductsController@massDestroy')->name('products.massDestroy');

    Route::resource('products', 'App\Http\Controllers\Admin\ProductsController');

    Route::get('test', 'App\Http\Controllers\Admin\AdminOrderController@testFunction')->name('test');
});