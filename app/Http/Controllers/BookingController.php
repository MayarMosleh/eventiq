<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function create(Request $request){
        $validate = $request->validate([

        ]);
        $booking = Booking::create($request->all());
    }
}
