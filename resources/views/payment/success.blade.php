<x-guest-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg mx-auto">
            <div class="text-center">
                <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <h2 class="text-2xl font-bold mb-4">Paiement réussi !</h2>
                <p class="text-gray-600 mb-6">Merci pour votre paiement. Votre transaction a été complétée avec succès.
                </p>

                <div class="mb-6">
                    <p class="text-sm text-gray-500">ID de transaction : {{ $session->id }}</p>
                    @if ($session->customer_details->email)
                        <p class="text-sm text-gray-500">Email : {{ $session->customer_details->email }}</p>
                    @endif
                </div>

                <a href="/marketing/events/{{ $eventId }}"
                    class="inline-block bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                    Retour à l'événement
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
