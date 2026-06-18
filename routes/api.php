<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\DriverVehicleController;
use App\Http\Controllers\DriverProfileController;
use App\Http\Controllers\LoaderController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ==================== Публичные маршруты ====================

// Авторизация
Route::post('/user/register', [UserController::class, 'register']);
Route::post('/user/login', [UserController::class, 'login']);

// ==================== Защищённые маршруты (требуют токен) ====================
Route::middleware('auth:sanctum')->group(function () {
    
    // Профиль пользователя
    Route::get('/user', [UserController::class, 'getCurrentUser']);
    Route::post('/logout', [UserController::class, 'logout']);
    Route::delete('/user', [UserController::class, 'destroy']);
    Route::put('/user/update/{id}', [UserController::class, 'update']);
    Route::put('/user/change-role', [UserController::class, 'changeRole']);
    Route::get('/user/balance', [UserController::class, 'getBalance']);

    Route::post('/user/test-deposit', [UserController::class, 'testDeposit']);
    Route::post('/user/test-withdraw', [UserController::class, 'testWithdraw']);

    // Техника водителя
    Route::get('/driver/vehicles', [DriverVehicleController::class, 'index']);
    Route::post('/driver/vehicles', [DriverVehicleController::class, 'store']);
    Route::delete('/driver/vehicles/{id}', [DriverVehicleController::class, 'destroy']);
    Route::get('/driver/profile', [DriverProfileController::class, 'show']);
    Route::put('/driver/profile', [DriverProfileController::class, 'update']);

    // Грузчик
    Route::get('/loader/orders/active', [LoaderController::class, 'activeOrders']);
    Route::get('/loader/orders/history', [LoaderController::class, 'historyOrders']);
    Route::get('/loader/stats', [LoaderController::class, 'stats']);
    

    // Заказы
    Route::post('/orders', [OrderController::class, 'store']);

    // Для заказчика
    Route::get('/customer/orders/active', [OrderController::class, 'customerActive']);
    Route::get('/customer/orders/history', [OrderController::class, 'customerHistory']);
    Route::post('/customer/orders/{id}/confirm', [OrderController::class, 'confirmByCustomer']);

    // Для водителя
    Route::get('/driver/orders/available', [OrderController::class, 'availableForDriver']);
    Route::get('/driver/orders/active', [OrderController::class, 'driverActive']);
    Route::get('/driver/orders/history', [OrderController::class, 'driverHistory']);
    Route::post('/driver/orders/{id}/accept', [OrderController::class, 'acceptByDriver']);
    Route::post('/driver/orders/{id}/complete', [OrderController::class, 'completeByDriver']);

    // Для грузчика
    Route::get('/loader/orders/available', [OrderController::class, 'availableForLoader']);
    Route::get('/loader/orders/active', [OrderController::class, 'loaderActive']);
    Route::get('/loader/orders/history', [OrderController::class, 'loaderHistory']);
    Route::post('/loader/orders/{id}/accept', [OrderController::class, 'acceptByLoader']);
    Route::post('/loader/orders/{id}/complete', [OrderController::class, 'completeByLoader']);
    
    // Статьи (позже)
    // Route::post('/articles', [ArticleController::class, 'store']);
    // Route::put('/articles/{article}', [ArticleController::class, 'update']);
    // Route::delete('/articles/{article}', [ArticleController::class, 'destroy']);

    // Отзывы (позже)
    // Route::post('/reviews', [ReviewController::class, 'store']);
});