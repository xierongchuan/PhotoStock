<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'login'])->name('login');
Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
