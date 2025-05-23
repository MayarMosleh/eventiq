<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventRequestController extends Controller
{
    // إنشاء طلب جديد من البروفايدر
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
        $exists = EventRequest::where('provider_id', auth()->id())
    ->where('event_data->title', $request->title)
        ->where('status', 'pending')
        ->exists();

    if ($exists) {
        return response()->json(['message' => 'تم إرسال طلب مماثل من قبل'], 409);
    }


        EventRequest::create([
            'provider_id' => Auth::id(),
            'event_data' => [
                'title' => $request->title,
                'description' => $request->description,
            ],
        ]);

        return response()->json(['message' => 'تم إرسال الطلب بنجاح. بانتظار الموافقة.']);
    }

    // عرض الطلبات (للاستخدام الإداري)
    public function index()
    {
       $requests = EventRequest::latest()->take(5)->get();
        return response()->json($requests);
    }

    // قبول أو رفض طلب
    public function update(Request $request, $id)
{

    $eventRequest = EventRequest::findOrFail($id);

    $request->validate([
        'status' => 'required|in:approved,rejected',
        'admin_response' => 'nullable|string',
    ]);

    $eventRequest->status = $request->status;
    $eventRequest->admin_response = $request->admin_response;
    $eventRequest->admin_id = Auth::id();
    $eventRequest->save();

    if ($request->status === 'approved') {
        Event::create([
            'provider_id' => $eventRequest->provider_id,
            'title' => $eventRequest->event_data['title'],
            'description' => $eventRequest->event_data['description'],
            'status' => 'approved',
        ]);
    }

    return response()->json(['message' => 'تمت مراجعة الطلب.']);
}
}