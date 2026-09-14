<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;


/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    return view('app');

});


/*
|--------------------------------------------------------------------------
| Product Custom Routes
|--------------------------------------------------------------------------
|
| These routes must be defined before the resource routes.
|
*/


// Analytics
Route::get(
    '/products-analytics',
    [ProductController::class, 'analytics']
);


// CSV Export
Route::get(
    '/products-export',
    [ProductController::class, 'export']
);


// Bulk Delete
Route::post(
    '/products-bulk-delete',
    [ProductController::class, 'bulkDelete']
);


// Duplicate Product
Route::post(
    '/products/{id}/duplicate',
    [ProductController::class, 'duplicate']
);

Route::post('/products-bulk-update', [ProductController::class, 'bulkUpdate']);
Route::get('/products-trash', [ProductController::class, 'trash']);
Route::post('/products/{id}/restore', [ProductController::class, 'restore']);
Route::post('/products-import', [ProductController::class, 'import']);


/*
|--------------------------------------------------------------------------
| Product Resource Routes
|--------------------------------------------------------------------------
*/

Route::resource(
    'products',
    ProductController::class
);


/*
|--------------------------------------------------------------------------
| Color Resource Routes
|--------------------------------------------------------------------------
*/

Route::resource(
    'colors',
    ColorController::class
);

Route::resource('categories', CategoryController::class)->only(['index', 'store', 'destroy']);
Route::resource('brands', BrandController::class)->only(['index', 'store', 'destroy']);