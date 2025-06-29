<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVenueRequest;
use App\Http\Requests\UpdateVenueRequest;
use App\Models\Company;
use App\Models\ServiceImage;
use App\Models\venue;
use App\Models\VenueImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VenueController extends Controller
{

    public function store(StoreVenueRequest $request): JsonResponse
    {
        $user = Auth::user();

        $company = $user->company;

        if (!$company) {
            return response()->json(['message' => __('venue.you do not have a company')], 400);
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
            return response()->json(['message' => __('venue.unauthorized')], 403);
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
            return response()->json(['message' => __('venue.you do not have a company')], 400);
        }

        $venues = $company->venues;

        return response()->json($venues, 200);
    }


    public function show($id)
    {
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

    public function getCompanyVenues($companyId)
    {
        $company = Company::with('venues')->findOrFail($companyId);

        return response()->json([
            'company_id' => $company->id,
            'venues' => $company->venues
        ]);
    }


    public function destroy($id): JsonResponse
    {
        $user = Auth::user();

        $venue = venue::findOrFail($id);

        if ($venue->company_id !== $user->company_id) {
            return response()->json(['message' => __('venue.unauthorized')], 403);
        }

        $venue->delete();

        return response()->json(['message' => __('venue.the venue has been deleted successfully')], 200);
    }


    public function AddImage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'venue_id' => 'required|exists:venues,id',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $path = $request->file('image')->store('venue_images', 'public');

        $record = VenueImage::create([
            'venue_id' => $validated['venue_id'],
            'image_url' => $path,
        ]);

        return response()->json([
            'message' => __('service.Image uploaded successfully'),
            'image' => $path,
        ]);
    }

    public function getImages(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'venue_id' => 'required|exists:venues,id',
        ]);

        $images = VenueImage::where('venue_id', $validated['venue_id'])->get();

        return response()->json([
            'images' => $images,
        ], 200);
    }
}
