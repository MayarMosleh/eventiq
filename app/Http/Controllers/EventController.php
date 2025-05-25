<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();
        $company = $user->company;

        if (!$company) {
    return response()->json(['error' => 'لا يوجد ملف شركة مرتبط بالحساب.'], 400);
}


        $request->validate([
            'event_name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
        $exists = Event::where('company_id', $company->id)
     ->where('event_name', $request->event_name)
    ->whereIn('status', ['pending', 'rejected'])
    ->exists();

    if ($exists) {
        return response()->json(['message' => 'تم إرسال طلب مماثل من قبل'], 409);
    }


        $event = Event::create([
    'event_name' => $request->event_name,
    'description' => $request->description,
    'company_id' => $company->id,
    'status' => 'pending',
]);

        return response()->json(['message' => 'تم إرسال الطلب بنجاح. بانتظار الموافقة.']);
    }
    }

