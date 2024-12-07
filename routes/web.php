<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailTestController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/email-test', [EmailTestController::class, 'showForm'])->name('email.form');
Route::post('/email-test', [EmailTestController::class, 'sendTest'])->name('email.send');
