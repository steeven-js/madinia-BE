<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Stripe\Stripe;
use Stripe\Product;
use Stripe\Price;

class StripeEventController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createEvent(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
        ]);

        try {
            // Créer un produit Stripe
            $event = Product::create([
                'name' => $request->name,
                'type' => 'service',
            ]);

            // Créer un prix pour le produit
            $price = Price::create([
                'product' => $event->id,
                'unit_amount' => $request->price * 100, // Convertir en centimes
                'currency' => 'eur',
            ]);

            return response()->json([
                'id' => $event->id,
                'price_id' => $price->id,
                'name' => $event->name,
                'price' => $request->price,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateEvent(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
        ]);

        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

            // Mettre à jour le produit
            $event = $stripe->products->update(
                $id,
                ['name' => $request->name]
            );

            // Récupérer le prix actuel
            $prices = Price::all(['product' => $id]);
            $currentPrice = $prices->data[0];

            // Si le prix est différent, créer un nouveau prix
            if ($currentPrice->unit_amount !== $request->price * 100) {
                // Désactiver l'ancien prix
                $stripe->prices->update($currentPrice->id, ['active' => false]);

                // Créer le nouveau prix
                $price = Price::create([
                    'product' => $event->id,
                    'unit_amount' => $request->price * 100,
                    'currency' => 'eur'
                ]);
            }

            return response()->json([
                'id' => $event->id,
                'price_id' => $price->id ?? $currentPrice->id,
                'name' => $event->name,
                'price' => $request->price
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteEvent($id)
    {
        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

            // Désactiver les prix
            $prices = $stripe->prices->all(['product' => $id]);
            foreach ($prices->data as $price) {
                $stripe->prices->update($price->id, ['active' => false]);
            }

            // Archiver le produit
            $stripe->products->update($id, ['active' => false]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
