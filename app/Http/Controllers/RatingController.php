<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRatingRequest;
use App\Models\Booking;
use App\Models\Rating;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;

class RatingController extends Controller
{


    public function store(StoreRatingRequest $request)
    {
        $user = Auth::user();

        $booking = Booking::where('id', $request->booking_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$booking) {
            return response()->json(['message' => 'You can only rate your own bookings.'], 403);
        }

        // نحصل على البيانات بعد التحقق
        $validated = $request->validated();

        $rating = Rating::create([
            'user_id' => $user->id,
            'booking_id' => $validated['booking_id'],
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json(['message' => 'Rating submitted successfully.', 'data' => $rating], 201);
    }
}
