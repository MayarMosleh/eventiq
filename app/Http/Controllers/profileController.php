<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProfileRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Profile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class profileController extends Controller
{
    public function index()
    {
        $profile = Auth::user()->profile;
        return response()->json($profile, 200);
    }


    public function show()
    {
        $user = Auth::user();
        $profile = Profile::where('user_id', $user->id)->firstOrFail();
        return response()->json($profile, 200);
    }


    public function store(StoreProfileRequest $request)
    {
        $userId = Auth::user()->id;
        $validated = $request->validated();
        $validated['user_id'] = $userId;
        if ($request->hasFile('img')) {
            $path = $request->file('img')->store('my photo', 'public');
            $validated['img'] = $path;
        }
        $profile = Profile::create($validated);
        return response()->json($profile, 201);
    }


    public function destroy()
    {
        try {
            $profile = Auth::user()->profile;

            if (!$profile) {
                return response()->json(['error' =>__('Profile not found')], 404);
            }

            $profile->delete();
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' =>__('Model not found')], 404);
        } catch (\Exception $e) {
            return response()->json(['error' =>__('Something went wrong')], 500);
        }
    }


    public function update(UpdateProfileRequest $request)
    {
        $profile = Auth::user()->profile;

        if (!$profile) {
            return response()->json(['error' =>__('Profile not found')], 404);
        }

        $profile->update($request->validated());
        return response()->json($profile, 200);
    }
}
