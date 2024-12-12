<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Ajustez selon vos besoins d'authentification
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'firebaseId' => ['string', Rule::unique('events')->ignore($this->event)],
            'title' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'scheduled_date' => ['required', 'date'],
            'is_active' => ['required', 'boolean'],
            'status' => ['required', 'string', 'max:255'],
            'stripe_event_id' => ['nullable', 'string'],
            'stripe_price_id' => ['nullable', 'string']
        ];
    }
}
