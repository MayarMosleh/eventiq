<?php

namespace App\Http\Controllers;


use App\Models\Company;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{

    public function showEvents(): JsonResponse
    {
        $events = Event::where('status', 'approved')->select('id', 'event_name', 'description')->get();
        return response()->json($events, 200);
    }

}
