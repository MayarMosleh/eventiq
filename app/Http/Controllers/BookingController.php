<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingService;
use App\Models\Company;
use App\Models\Event;
use App\Models\Service;
use App\Models\venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function createBooking(Request $request): JsonResponse
    {
        $user = Auth::user();
        $bookingStatus = $user->bookings()->whereNull('status')->exists();
        if ($bookingStatus) {
            return response()->json(['message' => 'Please Confirm the previous reservation first'], 409);
        }
        $validateData = $request->validate([
            'start_time' => 'required',
            'end_time' => 'required',
            'booking_date' => 'required',
            'number_of_invites' => 'required',
        ]);

        $booking = Booking::create([
            'user_id' => Auth::user()->id,
            'start_time' => $validateData['start_time'],
            'end_time' => $validateData['end_time'],
            'booking_date' => $validateData['booking_date'],
            'number_of_invites' => $validateData['number_of_invites'],
        ]);
        return response()->json(['message' => 'Booking has been created','booking_id'=>$booking->id],201);
    }
    public function selectEvent(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'event_id' => 'required|exists:events,id',
        ]);
        if(!(is_null(Booking::find($validatedData['booking_id'])->event_id))){
            return response()->json(['message' => 'Event already Selected'], 409);
        }
        $event = Event::find($request->event_id);
        $booking = Booking::find($validatedData['booking_id']);
        $booking->update([
            'user_id' =>Auth::user()->id,
            'event_id' => $validatedData['event_id'],
            'event_name' => $event->event_name,
        ]);
        return response()->json(['message' => 'Event selected','booking_id'=>$booking->id,'event_id'=>$booking->event_id],201);
    }

    public function selectProvider(Request $request): JsonResponse
    {
        $validateDate = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'company_id' => 'required|exists:companies,id',
        ]);

        $company = Company::find($validateDate['company_id']);
        $booking = Booking::find($validateDate['booking_id']);
        if(!is_null($booking->company_id)){
            return response()->json(['message' => 'Company already selected'],409);
        }
         Booking::where('id',$validateDate['booking_id'])->update([
            'company_id' => $validateDate['company_id'],
            'company_name' => $company->company_name,
        ]);
        return response()->json(['message' => 'Provider selected'],201);
    }

    public function selectVenue(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'venue_id' => 'required|exists:venues,id',
        ]);
        $booking = Booking::find($validatedData['booking_id']);
        if (!is_null($booking->venue_id)) {
            return response()->json(['message' => 'Venue already Selected'], 409);
        }
        $venue = venue::find($validatedData['venue_id']);
        $price = $venue->venue_price + Booking::where('id',$validatedData['booking_id'])->value('total_price');
         Booking::where('id',$validatedData['booking_id'])->update([
            'venue_id' => $validatedData['venue_id'],
            'venue_name' => $venue->venue_name,
            'venue_address' => $venue->address,
            'venue_price' => $venue->venue_price,
            'total_price' => $price,
        ]);
        return response()->json(['message' => 'Venue selected'],201);

    }

    public function selectService(Request $request): JsonResponse
    {

        $validateDate = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'service_id' => 'required|exists:services,id',
        ]);

        $booking = Booking::find($validateDate['booking_id']);
        if ($booking->bookingServices()->where('service_id',$validateDate['service_id'])->exists()) {
            return response()->json(['message' => 'Service already selected'], 409);
        }
        $service = Service::find($validateDate['service_id']);
        BookingService::create([
            'booking_id' => $validateDate['booking_id'],
            'service_id' => $validateDate['service_id'],
            'service_name' => $service->service_name,
            'service_price' => $service->service_price,
            'service_description' => $service->service_description,
        ]);
        $price = $service->service_price + Booking::where('id',$validateDate['booking_id'])->value('total_price');
        Booking::where('id',$validateDate['booking_id'])->update([
            'total_price' => $price,
        ]);
        return response()->json(['message' => 'Service selected'],201);
    }

    public function confirmBooking(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);
        if(is_null(Booking::find($validatedData['booking_id'])->status)){
            if (Booking::where('id',$validatedData['booking_id'])->where('user_id',Auth::user()->id)){
                Booking::where('id',$validatedData['booking_id'])->update([
                    'status' => 'waiting',
                ]);
            }
            return response()->json(['message' => 'Booking are waiting'],201);
        }
        return response()->json(['message' => 'Booking is Waiting'], 409);
    }

}
