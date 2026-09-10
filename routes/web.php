<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ColorController;

Route::get('/', function () {
    return view('app');
});

Route::resource('products', ProductController::class);

Route::resource('colors', ColorController::class);

/*
|--------------------------------------------------------------------------
| Product Analytics
|--------------------------------------------------------------------------
*/
Route::get(
    '/products-analytics',
    [ProductController::class, 'analytics']
);