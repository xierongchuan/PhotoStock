<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/user', fn (Request $request) => $request->user());
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/images', [ImageController::class, 'index']);
    Route::post('/images', [ImageController::class, 'store']);
    Route::get('/images/{image}', [ImageController::class, 'show']);
    Route::delete('/images/{image}', [ImageController::class, 'destroy']);
});
