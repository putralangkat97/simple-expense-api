<?php

use Illuminate\Support\Facades\Route;

Route::controller(\App\Http\Controllers\API\Auth\AuthController::class)
    ->group(function () {
        Route::post('register', 'register');
        Route::post('login', 'login');

        Route::middleware(['auth:sanctum'])
            ->group(function () {
                Route::get('logout', 'logout');
            });
    });

Route::middleware(['auth:sanctum'])
    ->group(function () {
        Route::controller(\App\Http\Controllers\API\AccountController::class)
            ->prefix('account')
            ->group(function () {
                Route::get('/', 'index');
                Route::get('/{id}', 'index');
                Route::post('/', 'submit');
                Route::post('/{id}', 'submit');
                Route::delete('/{id}', 'delete');
            });
    });

Route::middleware(['auth:sanctum'])
    ->group(function () {
        Route::controller(\App\Http\Controllers\API\TransactionController::class)
            ->prefix('transaction')
            ->group(function () {
                Route::get('/', 'index');
                Route::get('/{id}', 'index');
                Route::post('/', 'submit');
                Route::post('/{id}', 'submit');
                Route::delete('/{id}', 'delete');
            });
    });
