<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVenueRequest;
use App\Http\Requests\UpdateVenueRequest;
use App\Models\venue;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;

class VenueController extends Controller
{

    public function store(StoreVenueRequest $request)
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

    public function update(UpdateVenueRequest $request, $id){
        $user = Auth::user();

        $venue = venue::findOrFail($id);

        if($venue->company_id !== $user->company_id){
            return response()->json(['message'=>'unauthorized'], 403);
        }

        $validated = $request->validated();
        $venue->update($validated);

        return response()->json($venue, 200);
    }

}
