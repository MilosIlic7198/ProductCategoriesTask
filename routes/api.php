<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DefaultController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->get('/authenticated', function (Request $request) {
    return true;
});

Route::get('/default-response', [DefaultController::class, 'getDefaultResponse']);

Route::get('/categories', [CategoryController::class, 'getCategories']);

Route::put('/categories/{id}', [CategoryController::class, 'updateCategory']);

Route::delete('/categories/{id}', [CategoryController::class, 'deleteCategory']);

Route::get('/products', [ProductController::class, 'getProducts']);

Route::get('/categories/{id}/products', [ProductController::class, 'getProductsOfCategory']);

Route::put('/products/{id}', [ProductController::class, 'updateProduct']);

Route::delete('/products/{id}', [ProductController::class, 'deleteProduct']);

Route::get('/generate-csv/{categoryId}', [ProductController::class, 'generateCsv']);
