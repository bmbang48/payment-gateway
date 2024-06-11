<?php

use App\Http\Controllers\DonationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/donation', [DonationController::class, 'store']);
Route::post('/midtrans/notification', [DonationController::class, 'notifcation']);
