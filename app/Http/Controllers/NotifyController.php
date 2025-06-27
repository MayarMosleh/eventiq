<?php

namespace App\Http\Controllers;

use App\Models\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class NotifyController extends Controller
{
    public function index()
{
    $notifications =Notify::where('user_id', Auth::id())
        ->orderBy('created_at', 'desc')
        ->paginate(15); 

    return response()->json([
        'notifications' => $notifications
    ]);
}
public function destroy($id)
{
    $deleted =Notify::where('id', $id)
        ->where('user_id', Auth::id()) 
        ->delete();

    if ($deleted) {
        return response()->json(['message' => 'Notification deleted successfully.']);
    }

    return response()->json(['message' => 'Notification not found or unauthorized.'], 404);
}
}
