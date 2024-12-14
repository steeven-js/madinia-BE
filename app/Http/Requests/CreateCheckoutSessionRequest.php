<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCheckoutSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'eventId' => 'required|string',
            'priceId' => 'required|string|starts_with:price_',
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'returnUrl' => 'required|url',
            'email' => 'nullable|email'
        ];
    }

    public function messages(): array
    {
        return [
            'eventId.required' => 'L\'ID de l\'événement est requis',
            'priceId.required' => 'L\'ID du prix Stripe est requis',
            'priceId.starts_with' => 'L\'ID du prix doit commencer par "price_"',
            'title.required' => 'Le titre est requis',
            'price.required' => 'Le prix est requis',
            'price.min' => 'Le prix doit être supérieur à 0',
            'returnUrl.required' => 'L\'URL de retour est requise',
            'returnUrl.url' => 'L\'URL de retour doit être une URL valide',
            'email.email' => 'L\'email doit être une adresse valide'
        ];
    }
}
