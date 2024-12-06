<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StripeEventController;
use App\Http\Controllers\Api\IntercomController;
use App\Http\Controllers\Api\ContactMailController;
// use App\Http\Controllers\API\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('authApi')->group(function () {
    Route::apiResource("contacts", ContactMailController::class);
    Route::post('/generate-hmac', [IntercomController::class, 'generateHmac']);
    Route::post('stripe/create-event', [StripeEventController::class, 'createEvent']);
    Route::put('stripe/update-event/{id}', [StripeEventController::class, 'updateEvent']);
    Route::delete('stripe/delete-event/{id}', [StripeEventController::class, 'deleteEvent']);
});

// Route::apiResource("users", UserController::class);

