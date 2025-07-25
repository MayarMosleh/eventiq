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
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{

    public function showProviders(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
        ]) ;

        $providers = DB::table('company_events')
            ->join('companies', 'company_events.company_id', '=', 'companies.id')
            ->where('company_events.event_id', $validatedData['event_id'])
            ->select(
                'company_events.id as company_events_id',
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
                return response()->json(['message' => __('company.you already have a company.')], 409);
            }

            $validated = $request->validated();
            $validated['user_id'] = $user->id;

            if ($request->hasFile('company_image')) {
                $path = $request->file('company_image')->store('Company Photos', 'public');
                $validated['company_image'] = $path;
            }

            $company = Company::create($validated);
            return response()->json($company, 201);
        }

        return response()->json(['message' => __('company.unauthorized')], 403);
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

    public function updateInfo(Request $request, $id)
    {
        $user = Auth::user();
        $company = Company::findOrFail($id);

        if ($company->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'company_name' => [
                'required',
                'string',
                Rule::unique('companies')->ignore($company->id),
            ],
            'description' => 'required|string',
        ]);

        $company->update($validated);

        return response()->json([
            'message' => 'Company info updated successfully.',
            'company' => $company,
        ]);
    }

    public function updateImage(Request $request, $id)
    {
        $user = Auth::user();
        $company = Company::findOrFail($id);

        if ($company->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'company_image' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($request->hasFile('company_image')) {
            $path = $request->file('company_image')->store('Company Photos', 'public');
            $company->update(['company_image' => $path]);
        }

        return response()->json([
            'message' => 'Company image updated successfully.',
            'company' => $company,
        ]);
    }



    public function destroy($id)
    {
        $user = auth()->user();


        if ($user->role !== 'provider') {
            return response()->json(['message' => __('company.unauthorized')], 403);
        }

        $company = Company::findOrFail($id);


        if ($company->user_id !== $user->id) {
            return response()->json(['message' => __('company.Forbidden')], 403);
        }

        $company->delete();


        return response()->json(['message' => __('company.company deleted')], 200);
    }


    public function search(Request $request)
    {
        $company_name = $request->input('company_name');

        $companies = Company::where('company_name', 'LIKE', "%{$company_name}%")->get();

        if ($companies->isEmpty()) {
            return response()->json(['message' => __('company.No companies found')], 404);
        }

        return response()->json(['companies' => $companies], 200);
    }

    public function getAverageRating(Company $company)
    {
        $averageRating = $company->ratings()->avg('rating');

        return response()->json([
            'company_id' => $company->id,
            'average_rating' => $averageRating ?? 0,
        ]);
    }

    public function addEventToCompany(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return response()->json(['message' => __('company.No companies found')], 400);
        }

        $validated = $request->validate([
            'event_ids' => 'required|array',
            'event_ids.*' => 'exists:events,id',
        ]);

        $existingEventIds = $company->events()->pluck('events.id')->toArray();
        $incomingEventIds = $validated['event_ids'];

        // نشوف إذا في فعاليات مكررة
        $duplicates = array_intersect($existingEventIds, $incomingEventIds);

        if (!empty($duplicates)) {
            return response()->json([
                'message' => __('company.Some events are already added to your company.'),
                'duplicate_event_ids' => array_values($duplicates)
            ], 409);
        }

        $company->events()->attach($incomingEventIds);

        return response()->json(['message' => __('company.Events added to company successfully.')], 200);
    }

    public function indexCompanyEvents($companyId)
    {
        $company = Company::with('events')->find($companyId);

        if (!$company) {
            return response()->json(['message' => 'Company not found.'], 404);
        }

        return response()->json([
            'company_id' => $company->id,
            'company_name' => $company->company_name,
            'events' => $company->events
        ]);
    }
}
