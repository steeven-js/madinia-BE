<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailTestController;
use App\Http\Controllers\StripeCheckoutController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/email-test', [EmailTestController::class, 'showForm'])->name('email.form');
Route::post('/email-test', [EmailTestController::class, 'sendTest'])->name('email.send');

Route::get('/payment/success', function (Request $request) {
    return view('payment.success', [
        'session_id' => $request->session_id
    ]);
})->name('payment.success');
