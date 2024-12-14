<?php

namespace App\Http\Controllers;

use Stripe\Stripe;
use App\Models\Event;
use App\Models\EventPayment;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\Log;

class StripeCheckoutController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.test.secret'));
    }

    public function createCheckoutSession(Request $request)
    {
        $request->validate([
            'eventId' => 'required|string',
            'priceId' => 'required|string',
            'title' => 'required|string',
            'price' => 'required|numeric',
            'returnUrl' => 'required|string' // URL de retour ajoutée
        ]);

        try {
            $event = Event::where('firebaseId', $request->eventId)->firstOrFail();

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $request->priceId,
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $request->returnUrl, // Utilisation directe de l'URL de retour
                'metadata' => [
                    'event_id' => $event->id,
                    'firebase_id' => $request->eventId
                ]
            ]);

            return response()->json([
                'url' => $session->url
            ]);

        } catch (\Exception $e) {
            Log::error('Stripe session creation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function handleSuccess(Request $request)
    {
        try {
            $session = Session::retrieve($request->session_id);

            EventPayment::create([
                'event_id' => $session->metadata->event_id,
                'stripe_payment_id' => $session->payment_intent,
                'stripe_customer_id' => $session->customer,
                'amount' => $session->amount_total / 100,
                'currency' => $session->currency,
                'status' => 'completed',
                'payment_method_type' => 'card',
                'paid_at' => now()
            ]);

            // Rediriger vers la page de succès du frontend
            return redirect()->away($this->getFrontendUrl() . '/payment/success');

        } catch (\Exception $e) {
            Log::error('Payment recording error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->away($this->getFrontendUrl() . '/payment/error');
        }
    }

    private function getFrontendUrl()
    {
        return config('app.frontend_url', 'http://localhost:3001');
    }
}
