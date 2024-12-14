<?php

namespace App\Http\Controllers;

use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\Event;
use App\Models\EventPayment;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\CreateCheckoutSessionRequest;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;

class StripeCheckoutController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.test.secret'));
    }

    public function createCheckoutSession(CreateCheckoutSessionRequest $request): JsonResponse
    {
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
                'cancel_url' => $request->returnUrl,
                'metadata' => [
                    'event_id' => $event->id,
                    'firebase_id' => $request->eventId,
                    'title' => $request->title,
                    'price' => $request->price
                ],
                'customer_email' => $request->email ?? null,
                'client_reference_id' => $event->id
            ]);

            return response()->json([
                'url' => $session->url,
                'sessionId' => $session->id
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe session creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'error' => 'Payment session creation failed',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    }

    public function paymentSuccess(Request $request)
    {
        $sessionId = $request->get('session_id');

        if (!$sessionId) {
            return redirect()->route('welcome')->with('error', 'Session de paiement invalide');
        }

        try {
            $session = Session::retrieve($sessionId);
            $eventId = $session->metadata->firebase_id;

            return view('payment.success', [
                'session' => $session,
                'eventId' => $eventId
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving payment session', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('welcome')->with('error', 'Une erreur est survenue');
        }
    }

    public function handleWebhook(Request $request)
    {
        $endpoint_secret = config('services.stripe.test.webhook');

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            Log::error('Webhook error while parsing basic request.', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Webhook signature verification failed.', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => $e->getMessage()], 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                Log::info('Session completed', [
                    'session_id' => $session->id,
                    'customer_email' => $session->customer_details->email ?? null,
                    'event_id' => $session->metadata->event_id ?? null,
                    'firebase_id' => $session->metadata->firebase_id ?? null
                ]);
                break;
            default:
                Log::info('Received unknown event type ' . $event->type);
        }

        return response()->json(['status' => 'success']);
    }
}
