<?php

use App\Http\Controllers\DonationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DonationController::class, 'index']);
Route::get('/donation', [DonationController::class, 'create']);

// Route::post('/donation', [DonationController::class, 'store']);
