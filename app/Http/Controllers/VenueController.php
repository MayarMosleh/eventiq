<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVenueRequest;
use App\Http\Requests\UpdateVenueRequest;
use App\Models\venue;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VenueController extends Controller
{

    public function store(StoreVenueRequest $request): JsonResponse
    {
        $user = Auth::user();

        $company = $user->company;

        if (!$company) {
            return response()->json(['message' => 'You don\'t have a company.'], 400);
        }

        $validated = $request->validated();
        $validated['company_id'] = $company->id;


        $venue = venue::create($validated);


        return response()->json($venue, 201);
    }

    public function update(UpdateVenueRequest $request, $id): JsonResponse
    {
        $user = Auth::user();

        $venue = venue::findOrFail($id);

        if ($venue->company_id !== $user->company_id) {
            return response()->json(['message' => 'unauthorized'], 403);
        }

        $validated = $request->validated();
        $venue->update($validated);

        return response()->json($venue, 200);
    }


    public function index(): JsonResponse
    {
        $user = Auth::user();

        $company = $user->company;

        if (!$company) {
            return response()->json(['message' => 'you don\'t have a company'], 400);
        }

        $venues = $company->venues;

        return response()->json($venues, 200);
    }


    public function show($id){
        $venue = venue::findOrFail($id);

        return response()->json($venue, 200);
    }


    public function showVenue(Request $request): JsonResponse
    {
        $validateData = $request->validate([
            'company_id' => 'required|exists:companies,id',
        ]);
        $venue = venue::where('company_id', $validateData['company_id'])->get();
        return response()->json($venue, 200);
    }


    public function destroy($id): JsonResponse
    {
        $user = Auth::user();

        $venue = venue::findOrFail($id);

        if ($venue->company_id !== $user->company_id) {
            return response()->json(['message' => 'unauthorized'], 403);
        }

        $venue->delete();

        return response()->json(['message' => 'the venue has been deleted successfully.'], 200);
    }
}
