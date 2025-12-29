<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ColorController;

Route::get('/', function () {
    return view('app');
});

Route::resource('products', ProductController::class);
Route::resource('colors', ColorController::class);
