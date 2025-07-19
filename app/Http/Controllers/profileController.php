<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProfileRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Profile;
use App\Models\User;
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
        $profile = Profile::where('user_id', $user->id)->first();

        $profileData = $profile ? $profile : [
            'id' => null,
            'created_at' => null,
            'updated_at' => null,
            'user_id' => $user->id,
            'phone' => null,
            'img' => null,
            'birthDate' => null,
        ];

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'profile' => $profileData
        ]);
    }








    public function store(StoreProfileRequest $request)
    {
        $userId = Auth::user()->id;

        // تشييك إذا اليوزر عندو بروفايل
        if (Profile::where('user_id', $userId)->exists()) {
            return response()->json([
                'message' => 'You already have a profile.'
            ], 409); // Conflict
        }

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
                return response()->json(['error' => __('profile.Profile not found')], 404);
            }

            $profile->delete();
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => __('profile.Model not found')], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => __('profile.Something went wrong')], 500);
        }
    }


    public function updateInfo(UpdateProfileRequest $request, $id)
    {
        $user = Auth::user();

        $profile = Profile::findOrFail($id);

        if ($user->id !== $profile->user_id) {
            return response()->json(['message' => 'unauthorized'], 403);
        }

        $validated = $request->validated();

        $profile->update($validated);

        return response()->json([
            'message' => 'Profile info updated successfully.',
            'profile' => $profile,
        ]);
    }

    public function updateImage(Request $request, $id)
    {
        $user = Auth::user();
        $profile = Profile::findOrFail($id);

        if ($profile->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'img' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($request->hasFile('img')) {
            $path = $request->file('img')->store('my photo', 'public');
            $profile->update(['img' => $path]);
        }

        return response()->json([
            'message' => 'Profile image updated successfully.',
            'company' => $profile,
        ]);
    }
}
