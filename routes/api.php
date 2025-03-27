<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\TaskController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->name('v1.')->group(function () {
    // مسیرهای احراز هویت
    Route::controller(AuthController::class)->name('auth.')->group(function() {
        Route::post('login', 'login')->name('login');
        Route::post('register', 'register')->name('register');
        Route::post('logout', 'logout')->middleware('auth:sanctum')->name('logout');
    });

    // مسیرهای تسک‌ها (محافظت‌شده)
    Route::middleware('auth:sanctum')->prefix('tasks')->group(function() {
        Route::apiResource('', TaskController::class)->parameters(['' => 'task']);
        Route::patch('{task}/toggle', [TaskController::class, 'toggleStatus'])->name('tasks.toggle');
    });

});

