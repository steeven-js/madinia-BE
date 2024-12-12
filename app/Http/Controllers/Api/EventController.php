<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use Illuminate\Validation\ValidationException;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $events = Event::orderBy('scheduled_date', 'desc')->get();
        return response()->json($events);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request): JsonResponse
    {
        $event = Event::create($request->validated());
        return response()->json($event, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $firebaseId): JsonResponse
    {
        $event = Event::where('firebaseId', $firebaseId)->first();
        return response()->json([
            'data' => $event,
            'exists' => !is_null($event)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, string $firebaseId): JsonResponse
    {
        try {
            $event = Event::where('firebaseId', $firebaseId)->first();
            if (!$event) {
                return response()->json([
                    'exists' => false,
                    'message' => 'Event not found'
                ]);
            }

            $validated = $request->validated();
            Log::info('Validated data:', $validated);  // Pour le debugging

            $event->update($validated);

            return response()->json([
                'data' => $event,
                'exists' => true
            ]);
        } catch (\Exception $e) {
            Log::error('Update error:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Update failed',
                'error' => $e->getMessage(),
                'errors' => $this->getValidationErrors($e)
            ], 422);
        }
    }

    private function getValidationErrors(\Exception $e)
    {
        if ($e instanceof ValidationException) {
            return $e->errors();
        }
        return null;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $firebaseId): JsonResponse
    {
        $event = Event::where('firebaseId', $firebaseId)->first();
        if ($event) {
            $event->delete();
        }
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
