<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\LocationController;
use App\Http\Controllers\API\MutationController;
use App\Http\Controllers\API\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:api')->group(function () {
    Route::apiResource('products', ProductController::class);
    Route::apiResource('locations', LocationController::class);
});
Route::middleware(['auth:api'])->group(function () {
    Route::post('locations/{location}/add-product', [LocationController::class, 'addProduct']);
    Route::post('locations/{location}/out-product', [LocationController::class, 'outProduct']);

    Route::get('/mutations/product/{product}', [MutationController::class, 'historyByProduct']);
    Route::get('/mutations/user/{user}', [MutationController::class, 'historyByUser']);
});
