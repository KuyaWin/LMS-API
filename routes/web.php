<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/welcome', function () {
    return view('welcome');
});

// PayMongo redirect routes for mobile app deep linking
Route::get('/payment/success', function () {
    $transactionId = request('transaction_id');
    $sourceId = request('source_id');
    
    return view('payment-redirect', [
        'status' => 'success',
        'transaction_id' => $transactionId,
        'source_id' => $sourceId,
    ]);
});

Route::get('/payment/failed', function () {
    $transactionId = request('transaction_id');
    $sourceId = request('source_id');
    
    return view('payment-redirect', [
        'status' => 'failed',
        'transaction_id' => $transactionId,
        'source_id' => $sourceId,
    ]);
});
