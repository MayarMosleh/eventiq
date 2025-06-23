<?php

namespace App\Http\Controllers;

use App\Actions\AddPriceAndCreatBookingVenue;
use App\Actions\CalculatePriceAndCreateBookingService;
use App\Models\Booking;
use App\Models\BookingService;
use App\Models\Company;
use App\Models\Event;
use App\Models\Service;
use App\Models\venue;
use App\Rules\ServiceQuantityAvailable;
use App\Services\BookingServiceCheck;
use App\Services\BookingVenueCheck;
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

        return response()->json(['message' => 'Booking has been created', 'booking_id' => $booking->id], 201);
    }


    public function selectEvent(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'event_id' => 'required|exists:events,id',
        ]);

        if (!(is_null(Booking::find($validatedData['booking_id'])->event_id))) {
            return response()->json(['message' => 'Event already Selected'], 409);
        }

        $event = Event::find($request->event_id);
        $booking = Booking::find($validatedData['booking_id']);
        $booking->update([
            'user_id' => Auth::user()->id,
            'event_id' => $validatedData['event_id'],
            'event_name' => $event->event_name,
        ]);

        return response()->json(['message' => 'Event selected', 'booking_id' => $booking->id, 'event_id' => $booking->event_id], 201);
    }


    public function selectProvider(Request $request): JsonResponse
    {
        $validateData = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'company_id' => 'required|exists:companies,id',
        ]);

        $company = Company::find($validateData['company_id']);
        $booking = Booking::find($validateData['booking_id']);

        if (!is_null($booking->company_id)) {
            return response()->json(['message' => 'Company already selected'], 409);
        }

        Booking::where('id', $validateData['booking_id'])->update([
            'company_id' => $validateData['company_id'],
            'company_name' => $company->company_name,
        ]);
        return response()->json(['message' => 'Provider selected'],201);
    }


    public function selectVenue(Request $request, BookingVenueCheck $check, AddPriceAndCreatBookingVenue $creatBookingVenue): JsonResponse
    {
        $validatedData = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'venue_id' => 'required|exists:venues,id',
        ]);

        $booking = Booking::find($validatedData['booking_id']);

        if ($booking->venue()->exists()) {
            return response()->json(['message' => 'Venue already Selected'], 409);
        }

        $checkVenue = $check->checkAvailability($validatedData['venue_id'], $booking->start_time, $booking->end_time, $booking->booking_date);

        if (!$checkVenue) {
            return response()->json(['message' => 'Venue not available'], 409);
        }

        $creatBookingVenue($booking, $validatedData);

        return response()->json(['message' => 'Venue selected'], 201);
    }


    public function selectService(Request $request, BookingServiceCheck $check): JsonResponse
    {

        $validateData = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'service_id' => 'required|exists:services,id',
            'service_quantity' => ['required', 'integer', 'min:1', new ServiceQuantityAvailable($request->input('service_id'))],
        ]);

        $booking = Booking::find($validateData['booking_id']);

        if ($booking->bookingServices()->where('service_id', $validateData['service_id'])->exists()) {
            return response()->json(['message' => 'Service already selected'], 409);
        }

        $checkService = $check->checkAvailability($validateData['service_id'], $booking->start_time, $booking->end_time, $validateData['service_quantity'], $booking->booking_date);

        if (!$checkService) {
            return response()->json(['message' => 'Service not available'], 409);
        }

        $total_price = (new CalculatePriceAndCreateBookingService)($booking, $validateData);
        $newTotalPrice = $booking->total_price + $total_price;
        $booking->update([
            'total_price' => $newTotalPrice,
        ]);

        return response()->json(['message' => 'Service selected'], 201);
    }


    public function deleteServiceBooking(Request $request): JsonResponse
    {
        $validateData = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'service_id' => 'required|exists:booking_services,id',
        ]);

        $bookingService = BookingService::where('booking_id', $validateData['booking_id'])->where('service_id', $validateData['service_id'])->first();
        $booking = Booking::find($validateData['booking_id']);
        $updatePrice = $booking->total_price - $bookingService->service_price;
        $booking->update([
            'total_price' => $updatePrice,
        ]);

        $bookingService->delete();

        return response()->json(['message' => 'Deleted successfully'], 200);
    }


    public function updateQuantityService(Request $request, BookingServiceCheck $check): JsonResponse
    {
        $validateData = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'service_id' => 'required|exists:services,id',
            'service_quantity' => ['required', 'integer', 'min:1', new ServiceQuantityAvailable($request->input('service_id'))],
        ]);

        $booking = Booking::find($validateData['booking_id']);
        $service = Service::where('id', $validateData['service_id'])->first();
        $serviceBooking = BookingService::where('booking_id', $validateData['booking_id'])->where('service_id', $validateData['service_id'])->first();

        if ($serviceBooking->service_quantity > $validateData['service_quantity']) {
            $newServicePrice = $service->service_price * $validateData['service_quantity'];
            $newTotalPrice = ($booking->total_price - $serviceBooking->service_price) + $newServicePrice;
            $serviceBooking->update([
                'service_price' => $newServicePrice,
                'service_quantity' => $validateData['service_quantity'],
            ]);
            $booking->update([
                'total_price' => $newTotalPrice,
            ]);

            return response()->json(['message' => 'Quantity Updated'], 200);
        } elseif ($serviceBooking->service_quantity < $validateData['service_quantity']) {
            $checkService = $check->checkAvailability($validateData['service_id'], $booking->start_time, $booking->end_time, $validateData['service_quantity'], $booking->booking_date, $validateData['service_id']);

            if (!$checkService) {
                return response()->json(['message' => 'Service not available'], 409);
            }

            $lastPrice = $booking->total_price - $serviceBooking->service_price;

            $serviceBooking->update([
                'service_price' => $service->service_price * $validateData['service_quantity'],
                'service_quantity' => $validateData['service_quantity'],
            ]);

            $newTotalPrice = $lastPrice + $serviceBooking->service_price;
            $booking->update([
                'total_price' => $newTotalPrice,
            ]);

            return response()->json(['message' => 'Quantity Updated'], 200);
        }

        return response()->json(['message' => 'Quantity is already the same, no changes applied'], 200);
    }


    public function confirmBooking(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);

        if (is_null(Booking::find($validatedData['booking_id'])->status)) {
            if (Booking::where('id', $validatedData['booking_id'])->where('user_id', Auth::user()->id)) {
                Booking::where('id', $validatedData['booking_id'])->update([
                    'status' => 'waiting',
                ]);
            }

            return response()->json(['message' => 'Booking are waiting'], 201);
        }

        return response()->json(['message' => 'Booking is Waiting'], 409);
    }


    public function deleteVenue(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);

        $booking = Booking::findOrFail($validatedData['booking_id']);
        $venue = $booking->venue;

        $booking->update([
            'total_price' => $booking->total_price - $venue->venue_price,
        ]);

        $venue->delete();

        return response()->json(['message' => 'Venue Deleted'], 200);
    }


    public function cancelBooking(Request $request): JsonResponse
    {
       $validatedData = $request->validate([
           'booking_id' => 'required|exists:bookings,id',
       ]);
       Booking::findOrFail($validatedData['booking_id'])->delete();
       return response()->json(['message' => 'Booking Deleted'], 200);
    }

}
