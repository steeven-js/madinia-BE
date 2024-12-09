<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

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
        $event = Event::where('firebaseId', $firebaseId)->firstOrFail();
        return response()->json($event);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, string $firebaseId): JsonResponse
    {
        $event = Event::where('firebaseId', $firebaseId)->firstOrFail();
        $event->update($request->validated());
        return response()->json($event);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $firebaseId): JsonResponse
    {
        $event = Event::where('firebaseId', $firebaseId)->firstOrFail();
        $event->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
