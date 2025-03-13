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

//Category routes.
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'getCategories']);           //GET "/categories".
    Route::put('/{category}', [CategoryController::class, 'updateCategory']);      //PUT "/categories/{id}".
    Route::delete('/{category}', [CategoryController::class, 'deleteCategory']);   //DELETE "/categories/{id}".
    
    //Nested products under categories.
    Route::get('/{category}/products', [ProductController::class, 'getProductsOfCategory']);  //GET "/categories/{id}/products".
});

//Product routes.
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'getProducts']);             //GET "/products".
    Route::put('/{id}', [ProductController::class, 'updateProduct']);       //PUT "/products/{id}".
    Route::delete('/{id}', [ProductController::class, 'deleteProduct']);    //DELETE "/products/{id}".
    Route::get('/generate-csv/{categoryId}', [ProductController::class, 'generateCsv']);  //GET "/products/generate-csv/{categoryId}".
});
