<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EventRequest;
use App\Models\User;
use App\Notifications\FcmNotification;
use App\Services\FirebaseNotificationService;

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
        return response()->json(['error' => 'There is no company file associated with the account'], 400);
    }

        $alreadySubmitted = EventRequest::where('company_id', $company->id)
            ->where('event_name', $request->event_name)
            ->whereIn('status', ['pending', 'rejected'])
            ->exists();

    if ($alreadySubmitted) {
        return response()->json(['message' => 'this event has already been submitted and is either under review or rejected'], 409);
    }

    EventRequest::create([
        'company_id' => $company->id,
        'event_name' => $request->event_name,
        'description' => $request->description,
        'status' => 'pending',
    ]);

     $admin = User::where('role', 'admin')->first();
    if ($admin) {
        $admin->notify(new FirebaseNotificationService( 'New request to add an event', 'provider: ' . $user->name . ' submitted a request to add : ' . $request->event_name ));
    }

    return response()->json(['message' => 'The event has been submitted successfully and is being reviewed.'], 201);
    
}

public function adminResponse(Request $request,$id): JsonResponse
{
    $request->validate([
        'status' => 'required|in:approved,rejected',
    ]);

    $eventRequest = EventRequest::find($id);

    if (!$eventRequest) {
        return response()->json(['error' => 'request not found'], 404);
    }

    if ($eventRequest->status !== 'pending') {
        return response()->json(['message' => 'This request has been answered previously.'], 409);
    }

    $eventRequest->status = $request->status;
    $eventRequest->save();

    if ($request->status === 'approved') {
      
       $event =Event::create([
            'event_name' => $eventRequest->event_name,
            'description' => $eventRequest->description,
        ]);
         $event->companies()->attach($eventRequest->company_id);

         
         return response()->json(['message' => 'request accepted.']);
    }
    if ($request->status === 'rejected'){

        return response()->json(['message'=>'request rejected']);
    }
    
}
public function index(): JsonResponse//هاد للادمن
{
       $requests =EventRequest::latest()->take(5)->get();
        return response()->json($requests);
    }


    public function destroyAnsweredRequest($id): JsonResponse
    {
        $eventRequest = EventRequest::findOrFail($id);

        if(!$eventRequest) {
            return response()->json(['message'=>'الطلب غير موجود.'], 404);
        }

        if($eventRequest->status === 'pending'){
            return response()->json(['message'=>'لا يمكنك حذف طلب لم يتم الرد عليه.'], 403);
        }

        $eventRequest->delete();

        return response()->json(['message'=>'تم حذف الطلب بنجاح.'], 200);
    }

}
