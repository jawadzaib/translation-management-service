<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TranslationsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/



Route::middleware('auth:api')->group(function () {
    Route::get('translations/export', [TranslationsController::class, 'export']);
    Route::apiResource('translations', TranslationsController::class);
    Route::post('/logout', [AuthController::class, 'logout']);
}); 


Route::post('/login', [AuthController::class, 'login'])->name('login');