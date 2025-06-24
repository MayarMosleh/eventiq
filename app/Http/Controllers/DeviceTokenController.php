<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
public function store(Request $request)
{
    $request->validate([
        'token' => 'required|string',
        'platform' => 'required|in:web,mobile',
    ]);

    $user = $request->user();

    $user->deviceTokens()->updateOrCreate([
        'token' => $request->token,
    ], [
        'platform' => $request->platform,
    ]);

    return response()->json(['message' =>__('notif.Token saved')]);
}

}
