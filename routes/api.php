<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\IntercomController;
use App\Http\Controllers\StripeCheckoutController;
use App\Http\Controllers\Api\ContactMailController;
use App\Http\Controllers\Api\StripeEventController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Auth Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

// Protected Routes
Route::middleware('authApi')->group(function () {
    // Contact & Intercom Routes
    Route::apiResource('contacts', ContactMailController::class);
    Route::post('/generate-hmac', [IntercomController::class, 'generateHmac']);

    // Event Management Routes
    Route::apiResource('events', EventController::class);

    // Stripe Event Routes
    Route::prefix('stripe')->group(function () {
        // Event Management
        Route::get('/events', [StripeEventController::class, 'getEvents']);
        Route::get('/get-event/{id}', [StripeEventController::class, 'getEvent']);
        Route::post('/create-event', [StripeEventController::class, 'createEvent']);
        Route::put('/update-event/{id}', [StripeEventController::class, 'updateEvent']);
        Route::post('/update-image/{id}', [StripeEventController::class, 'updateImage']);
        Route::delete('/delete-event/{id}', [StripeEventController::class, 'deleteEvent']);

        // Checkout & Payment
        Route::controller(StripeCheckoutController::class)->group(function () {
            Route::post('/create-checkout-session', 'createCheckoutSession');
            Route::get('/payment-success', 'handleSuccess')->name('payment.success');
            Route::get('/payment-cancel', function () {
                return redirect()->to('/events')->with('error', 'Paiement annulé');
            })->name('payment.cancel');
        });
    });
});
