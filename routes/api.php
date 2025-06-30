
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CategoryController;





Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
  
// Authenticated routes
 Route::middleware('auth:sanctum')->group(function () {

  Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/store_categories', [CategoryController::class, 'store']);
    Route::get('/categories', [CategoryController::class, 'categories']);
    Route::post('/update_category/{id}', [CategoryController::class, 'update']);
    Route::delete('/delete_category/{id}', [CategoryController::class, 'destroy']);


    Route::get('/products', [ProductController::class, 'products']);
    Route::post('/store_product', [ProductController::class, 'store']);
    Route::post('/update_product/{id}', [ProductController::class, 'update']);
    Route::delete('/delete_product/{id}', [ProductController::class, 'destroy']);

    Route::get('/order-list-with-search', [OrderController::class, 'orderSearchList']);  // in the admin panel with 
    Route::get('/delivered/{id}', [OrderController::class, 'delivered']);  // in the admin pannel

    // Route::get('/user', function (Request $request) {
  
    //     return $request->user();                                                                                                         
    });
       
         Route::middleware('auth:sanctum')->get('/cancel_order/{id}', [OrderController::class, 'cancelOrder']); // home page
    
     Route::middleware('auth:sanctum')->get('/show_products', [ProductController::class, 'showProducts']); // at the home page 
     Route::middleware('auth:sanctum')->post('/add-to-cart/{id}', [ProductController::class, 'addToCart']);
     Route::middleware('auth:sanctum')->get('/show_cart', [ProductController::class, 'showCart']);
      Route::middleware('auth:sanctum')->delete('/remove_cart/{id}', [ProductController::class, 'remove_cart']);
      Route::middleware('auth:sanctum')->post('/cash-order', [ProductController::class, 'cash_order']);
    Route::middleware('auth:sanctum')->get('/product-detail/{id}', [ProductController::class, 'productDetail']); // for home page

    Route::middleware('auth:sanctum')->get('/search_product', [ProductController::class, 'search_product']); // for home
    Route::middleware('auth:sanctum')->post('/stripe-post/{totalprice}', [PaymentController::class, 'stripePayment'])->name('stripe.post'); // stripe payment