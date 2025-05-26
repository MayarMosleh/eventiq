<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    /*public function providers(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'event_id' => ['required','exists:events,id'],
        ]);

        $providers = Event::find($request->event_id)->companies()->select('companies.id','company_name',)->get()->makeHidden('pivot');
        return response()->json($providers,200);
    }*/
    public function providers(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
        ]);

        $providers = DB::table('company_event')
            ->join('companies', 'company_event.company_id', '=', 'companies.id')
            ->where('company_event.event_id', $validatedData['event_id'])
            ->select(
                'company_event.id as company_event_id',
                'companies.company_name',
                'companies.description',
            )
            ->get();

        return response()->json($providers, 200);
    }
}
