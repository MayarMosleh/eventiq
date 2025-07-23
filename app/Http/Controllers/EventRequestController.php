<?php

namespace App\Http\Controllers;

use App\Models\DeviceToken;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EventRequest;
use App\Models\Notify;
use App\Models\User;
use App\Notifications\FcmNotification;
use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\DB;

class EventRequestController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'event_name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
        ]);
        $user = auth()->user();
        $company = $user->company;

        if (!$company) {
            return response()->json(['error' => __('eventRequest.There is no company file associated with the account')], 400);
        }

        $alreadySubmitted = EventRequest::where('company_id', $company->id)
            ->where('event_name', $request->event_name)
            ->whereIn('status', ['pending', 'rejected'])
            ->exists();

        if ($alreadySubmitted) {
            return response()->json(['message' => __('eventRequest.this event has already been submitted and is either under review or rejected')], 409);
        }

        EventRequest::create([
            'company_id' => $company->id,
            'event_name' => $request->event_name,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        $admin = User::where('role', 'admin')->first();

        if ($admin) {
            $tokens = DeviceToken::where('user_id', $admin->id)

                ->pluck('token')
                ->toArray();

            if (count($tokens)) {
                $providerName = $user->name ?? 'Service Provider';
                $title = 'New Event Request';
                $body = "$providerName has submitted a request to add a new event to their company.";

                $firebaseService = new FirebaseNotificationService();
                $firebaseService->sendToTokens($tokens, $title, $body, [
                    'click_action' => 'EVENT_REQUEST_VIEW',
                    'request_type' => 'event',
                    'company_id' => $company->id,
                    'event_name' => $request->event_name
                ]);
                Notify::create([
                    'user_id' => $admin->id,
                    'title' => $title,
                    'body' => $body,
                    'data' => json_encode([
                        'company_id' => $company->id,
                        'event_name' => $request->event_name,
                    ]),
                    'read_at' => null,
                ]);
            }

            return response()->json(['message' => __('eventRequest.The event has been submitted successfully and is being reviewed.')], 201);
        }
    }

    public function adminResponse(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $eventRequest = EventRequest::find($id);

        if (!$eventRequest) {
            return response()->json(['error' => __('eventRequest.request not found')], 404);
        }

        if ($eventRequest->status !== 'pending') {
            return response()->json(['message' => __('eventRequest.This request has been answered previously.')], 409);
        }

        $eventRequest->status = $request->status;
        $eventRequest->save();


        $provider = $eventRequest->company->user ?? null;

        if (!$provider) {
        } else {
            $tokens = DeviceToken::where('user_id', $provider->id)
                ->pluck('token')
                ->toArray();

            if (count($tokens)) {
                $title = 'Event Request Update';
                $body = $request->status === 'approved'
                    ? "Your request for event '{$eventRequest->event_name}' has been approved."
                    : "Your request for event '{$eventRequest->event_name}' has been rejected.";

                $firebaseService = new FirebaseNotificationService();
                $firebaseService->sendToTokens($tokens, $title, $body, [
                    'click_action' => 'EVENT_REQUEST_RESPONSE',
                    'request_id' => $eventRequest->id,
                    'status' => $request->status,
                ]);

                Notify::create([
                    'user_id' => $provider->id,
                    'title' => $title,
                    'body' => $body,
                    'data' => json_encode([
                        'event_request_id' => $eventRequest->id,
                        'status' => $request->status,
                    ]),
                    'read_at' => null,
                ]);
            }
        }

        if ($request->status === 'approved') {
            $event = Event::create([
                'event_name' => $eventRequest->event_name,
                'description' => $eventRequest->description,
            ]);
            $event->companies()->attach($eventRequest->company_id);

            return response()->json(['message' => __('eventRequest.request accepted.')]);
        }

        if ($request->status === 'rejected') {
            return response()->json(['message' => __('eventRequest.request rejected')]);
        }

        return response()->json(['message' => __('eventRequest.Invalid status provided.')], 400);
    }

    public function index(): JsonResponse
    {
        $requests = EventRequest::latest()->take(5)->get();
        return response()->json($requests);
    }


    public function destroyAnsweredRequest($id): JsonResponse
    {
        $eventRequest = EventRequest::findOrFail($id);

        if (!$eventRequest) {
            return response()->json(['message' => __('eventRequest.request not found')], 404);
        }

        if ($eventRequest->status === 'pending') {
            return response()->json(['message' => __('eventRequest.You cannot delete a request that has not been answered')], 403);
        }

        $eventRequest->delete();

        return response()->json(['message' => __('eventRequest.Request deleted successfully.')], 200);
    }
}
