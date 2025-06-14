<?php

namespace App\Http\Controllers;


use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{

    public function showProviders(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
        ]);

        $providers = DB::table('company_events')
            ->join('companies', 'company_events.company_id', '=', 'companies.id')
            ->where('company_events.event_id', $validatedData['event_id'])
            ->select(
                'company_events.id as company_event_id',
                'companies.id as company_id',
                'companies.company_name',
                'companies.description',
            )
            ->get();

        return response()->json($providers, 200);
    }

    public function store(StoreCompanyRequest $request)
    {
        $user = Auth::user();

        if ($user->role === 'provider') {

            if ($user->company) {
                return response()->json(['message' => 'you already have one'], 409);
            }

            $validated = $request->validated();
            $validated['user_id'] = $user->id;

            $company = Company::create($validated);
            return response()->json($company, 201);
        }

        return response()->json(['message' => 'unauthorized'], 403);
    }


    public function show($id)
    {
        $company = Company::findOrFail($id);
        return response()->json($company, $status = 200);
    }

    public function index(): JsonResponse
    {
        $user = Auth::user();

        $companies = Company::all();


        return response()->json($companies, 200);
    }

    public function update(UpdateCompanyRequest $request, $id)
    {
        $user = Auth::user();

        if ($user->role === 'provider') {
            $company = Company::findOrFail($id);

            if ($company->user_id !== $user->id) {
                return response()->json(['message' => 'unauthorized'], 403);
            }

            $validated = $request->validated();
            $company->update($validated);

            return response()->json($company, 200);
        }

        return response()->json(['message' => 'unauthorized'], 403);
    }


    public function destroy($id)
    {
        $user = auth()->user();


        if ($user->role !== 'provider') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $company = Company::findOrFail($id);


        if ($company->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden - Not your company'], 403);
        }

        $company->delete();


        return response()->json(['message' => 'the company has been deleted'], 200);
    }


    public function search(Request $request)
    {
        $company_name = $request->input('company_name');

        $companies = Company::where('company_name', 'LIKE', "%{$company_name}%")->get();

        if ($companies->isEmpty()) {
            return response()->json(['message' => 'No companies found.'], 404);
        }

        return response()->json(['companies' => $companies], 200);
    }
}
