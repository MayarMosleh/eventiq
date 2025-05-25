<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
       public function store(StoreCompanyRequest $request)
    {
       $user = Auth::user();

    if ($user->role === 'provider') {
        $validated = $request->validated();
        $validated['user_id'] = $user->id; 

        $company = Company::create($validated);
        return response()->json($company, 201);
    }

    return response()->json(['message' => 'unauthorized'], 403);
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
