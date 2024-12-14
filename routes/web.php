<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmailTestController;

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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
