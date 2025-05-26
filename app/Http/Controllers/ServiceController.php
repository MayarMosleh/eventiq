<?php

namespace App\Http\Controllers;

use App\Models\CompanyEvent;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function ShowServices(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
           'company_event_id'=>['required', 'integer', 'exists:company_event,id'],
        ]);

        $services = Service::where('company_event_id', $validatedData['company_event_id'])->get();


        return response()->json($services,200);
    }
}
