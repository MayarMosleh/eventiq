<?php

namespace App\Http\Controllers;

use App\Models\CompanyEvent;
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

    $alreadySubmitted = CompanyEvent::where('company_id', $company->id)
        ->where('event_name', $request->event_name)
        ->whereIn('status', ['pending', 'rejected'])
        ->exists();

    if ($alreadySubmitted) {
        return response()->json(['message' => 'تم إرسال هذا الحدث مسبقًا وهو قيد المراجعة أو مرفوض.'], 409);
    }

    CompanyEvent::create([
        'company_id' => $company->id,
        'event_name' => $request->event_name,
        'description' => $request->description,
        'status' => 'pending',
    ]);

    return response()->json(['message' => 'تم إرسال الحدث بنجاح وهو قيد المراجعة.'], 201);
}

public function approveEvent($id)
{
    $companyEvent = CompanyEvent::findOrFail($id);

    if ($companyEvent->status !== 'pending') {
        return response()->json(['message' => 'لا يمكن معالجة الطلب.'], 400);
    }

    $event = Event::create([
        'event_name' => $companyEvent->event_name,
        'description' => $companyEvent->description,
    ]);

    $companyEvent->status = 'approved';
    $companyEvent->event_id = $event->id; 
    $companyEvent->save();

    return response()->json(['message' => 'تمت الموافقة على الحدث وإضافته بنجاح.'], 200);
}
public function index()
    {
       $requests =CompanyEvent::latest()->take(5)->get();
        return response()->json($requests);
    }
}
