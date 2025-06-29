<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{

    public function showEvents(): JsonResponse
    {
        $events = Event::select('id', 'event_name', 'description', 'image_url')->get();
        return response()->json($events, 200);
    }

    public function addEventAdmin(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'event_name' => 'required',
            'description' => 'required',
        ]);
        Event::create($validatedData);
        return response()->json(['massage' => __('event.Event created')], 200);
    }

    public function deleteEventAdmin(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'event_id' => 'required|integer|exists:events,id',
        ]);

        $event = Event::findOrFail($validatedData['event_id']);

        if ($event->image && Storage::disk('public')->exists($event->image)) {
            Storage::disk('public')->delete($event->image);
        }
        $event->delete();
        return response()->json(['message' => __('event.Event Deleted')], 200);
    }


    public function addImageEvent(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'event_id' => 'required|integer|exists:events,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $path = $request->file('image')->store('event_images', 'public');
        $event = Event::findOrFail($validatedData['event_id']);
        $event->image_url = $path;
        $event->save();
        return response()->json(['massage' => __('event.Image uploaded successfully'), 'image_url' => $event->image_url], 200);
    }


}
