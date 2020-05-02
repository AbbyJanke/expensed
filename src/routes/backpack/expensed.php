<?php

/*
|--------------------------------------------------------------------------
| AbbyJanke\expensed Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are
| handled by the Backpack\LogManager package.
|
*/

Route::group([
    'namespace'  => 'AbbyJanke\Expensed\app\Http\Controllers\Admin',
    'middleware' => ['web', config('backpack.base.middleware_key', 'admin')],
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
], function () {
    Route::crud('currency', 'CurrencyCrudController');
    Route::crud('expenses/category', 'CategoryCrudController');
    Route::crud('expenses/income', 'IncomeCrudController');
    Route::crud('expenses', 'ExpenseCrudController');
});
