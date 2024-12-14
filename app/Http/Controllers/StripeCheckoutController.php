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
                'success_url' => route('payment.success').'?session_id={CHECKOUT_SESSION_ID}',
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

            return redirect()->route('home')->with('error', 'Une erreur est survenue');
        }
    }

    public function handleWebhook()
    {
        // This is your Stripe CLI webhook secret for testing your endpoint locally.
        $endpoint_secret = 'whsec_ce9754c33f567d983d5e3a939cf1c7ab4d6e0c3c289e910f11786aa7582c33a9';

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return response('', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response('', 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;

                // log de $session
                Log::info('Session completed', [
                    'session_id' => $session->id,
                    'customer_email' => $session->customer_email,
                    'event_id' => $session->metadata->event_id,
                    'firebase_id' => $session->metadata->firebase_id
                ]);

                // $order = Order::where('session_id', $session->id)->first();
                // if ($order && $order->status === 'unpaid') {
                //     $order->status = 'paid';
                //     $order->save();
                //     // Send email to customer
                // }

                // ... handle other event types
            default:
                echo 'Received unknown event type ' . $event->type;
        }

        return response('');
    }

    protected function verifyWebhookSignature(Request $request): mixed
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        if (empty($sigHeader)) {
            throw new SignatureVerificationException('No signature header found');
        }

        return Webhook::constructEvent(
            $payload,
            $sigHeader,
            $webhookSecret
        );
    }

    protected function processStripeEvent($event): JsonResponse
    {
        return match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutSessionCompleted($event),
            'payment_intent.succeeded' => $this->handlePaymentIntentSucceeded($event),
            'payment_intent.payment_failed' => $this->handlePaymentIntentFailed($event),
            default => $this->handleUnknownEvent($event)
        };
    }

    protected function handleCheckoutSessionCompleted($event): JsonResponse
    {
        $session = $event->data->object;

        try {
            $payment = EventPayment::create([
                'event_id' => $session->metadata->event_id,
                'stripe_payment_id' => $session->payment_intent,
                'stripe_customer_id' => $session->customer,
                'amount' => $session->amount_total / 100,
                'currency' => $session->currency,
                'status' => 'completed',
                'payment_method_type' => $session->payment_method_types[0] ?? 'card',
                'paid_at' => now(),
                'metadata' => [
                    'event_title' => $session->metadata->title ?? null,
                    'customer_email' => $session->customer_details->email ?? null,
                    'session_id' => $session->id,
                    'firebase_id' => $session->metadata->firebase_id ?? null
                ]
            ]);

            return response()->json(['status' => 'success', 'payment' => $payment]);
        } catch (\Exception $e) {
            Log::error('Checkout session processing failed', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Checkout session processing failed',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    }

    protected function handlePaymentIntentSucceeded($event): JsonResponse
    {
        $paymentIntent = $event->data->object;

        try {
            $payment = EventPayment::where('stripe_payment_id', $paymentIntent->id)
                ->update([
                    'status' => 'completed',
                    'metadata->payment_intent_status' => $paymentIntent->status
                ]);

            return response()->json(['status' => 'success', 'payment' => $payment]);
        } catch (\Exception $e) {
            Log::error('Payment intent success processing failed', [
                'payment_intent_id' => $paymentIntent->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Payment intent processing failed',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    }

    protected function handlePaymentIntentFailed($event): JsonResponse
    {
        $paymentIntent = $event->data->object;

        try {
            $payment = EventPayment::where('stripe_payment_id', $paymentIntent->id)
                ->update([
                    'status' => 'failed',
                    'metadata->failure_reason' => $paymentIntent->last_payment_error->message ?? null,
                    'metadata->payment_intent_status' => $paymentIntent->status
                ]);

            return response()->json(['status' => 'success', 'payment' => $payment]);
        } catch (\Exception $e) {
            Log::error('Payment intent failure processing failed', [
                'payment_intent_id' => $paymentIntent->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Payment failure processing failed',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    }

    protected function handleUnknownEvent($event): JsonResponse
    {
        Log::info('Unhandled Stripe webhook event', [
            'type' => $event->type,
            'id' => $event->id
        ]);

        return response()->json(['status' => 'ignored', 'type' => $event->type]);
    }
}
