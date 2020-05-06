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
    'prefix'     => config('backpack.base.route_prefix', 'admin').'/money',
], function () {
    Route::crud('income', 'IncomeCrudController');
    Route::crud('expenses', 'ExpenseCrudController');
    Route::crud('reports', 'ReportsCrudController');
    Route::get('reports/ajax-currency-options', 'ReportsCrudController@currencyOptions');
    Route::crud('categories', 'CategoryCrudController');
    Route::crud('currencies', 'CurrencyCrudController');
});
