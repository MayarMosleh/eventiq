<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EventRequest;

class EventRequestController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();
        $company = $user->company;

        if (!$company) {
            return response()->json(['error' => 'لا يوجد ملف شركة مرتبط بالحساب.'], 400);
        }

        $alreadySubmitted = EventRequest::where('company_id', $company->id)
            ->where('event_name', $request->event_name)
            ->whereIn('status', ['pending', 'rejected'])
            ->exists();

        if ($alreadySubmitted) {
            return response()->json(['message' => 'تم إرسال هذا الحدث مسبقًا وهو قيد المراجعة أو مرفوض.'], 409);
        }

        EventRequest::create([
            'company_id' => $company->id,
            'event_name' => $request->event_name,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'تم إرسال الحدث بنجاح وهو قيد المراجعة.'], 201);
    }

    public function adminResponse(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $eventRequest = EventRequest::find($id);

        if (!$eventRequest) {
            return response()->json(['error' => 'الطلب غير موجود.'], 404);
        }

        if ($eventRequest->status !== 'pending') {
            return response()->json(['message' => 'تم الرد على هذا الطلب مسبقًا.'], 409);
        }

        $eventRequest->status = $request->status;
        $eventRequest->save();

        if ($request->status === 'approved') {

            $event = Event::create([
                'event_name' => $eventRequest->event_name,
                'description' => $eventRequest->description,
            ]);
            $event->companies()->attach($eventRequest->company_id);
        }

        return response()->json(['message' => 'تم تحديث حالة الطلب بنجاح.']);
    }


    public function index(): JsonResponse //هاد للادمن
    {
        $requests = EventRequest::latest()->take(5)->get();
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
