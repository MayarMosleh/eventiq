<?php

namespace App\Http\Controllers;

use App\Actions\AddPriceAndCreatBookingVenue;
use App\Actions\CalculatePriceAndCreateBookingService;
use App\Models\Booking;
use App\Models\BookingService;
use App\Models\Company;
use App\Models\DeviceToken;
use App\Models\Event;
use App\Models\Notify;
use App\Models\Service;
use App\Models\venue;
use App\Rules\CheckCompanyEvent;
use App\Rules\CheckCompanyService;
use App\Rules\CheckCompanyVenue;
use App\Rules\CheckUserBooking;
use App\Rules\ServiceQuantityAvailable;
use App\Services\BookingServiceCheck;
use App\Services\BookingVenueCheck;
use App\Services\FirebaseNotificationService;
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
            return response()->json(['message' =>__('booking.Please Confirm the previous reservation first')], 409);
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

        return response()->json(['message' =>__('booking.Booking has been created'), 'booking_id' => $booking->id], 201);
    }


    public function selectEvent(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id', new CheckUserBooking()],
            'event_id' => 'required|exists:events,id',
        ]);


        if (!(is_null(Booking::find($validatedData['booking_id'])->event_id))) {
            return response()->json(['message' =>__('booking.Event already Selected')], 409);
        }

        $event = Event::find($request->event_id);
        $booking = Booking::find($validatedData['booking_id']);
        $booking->update([
            'user_id' => Auth::user()->id,
            'event_id' => $validatedData['event_id'],
            'event_name' => $event->event_name,
        ]);

        return response()->json(['message' =>__('booking.Event selected'), 'booking_id' => $booking->id, 'event_id' => $booking->event_id], 201);
    }


    public function selectProvider(Request $request): JsonResponse
    {
        $validateData = $request->validate([
            'booking_id' => ['required','exists:bookings,id',new CheckUserBooking()],
            'company_id' => ['required','exists:companies,id',new CheckCompanyEvent($request->booking_id)]
        ]);

        $company = Company::find($validateData['company_id']);
        $booking = Booking::find($validateData['booking_id']);

        if (!is_null($booking->company_id)) {
            return response()->json(['message' =>__('booking.Company already selected')], 409);
        }

        Booking::where('id', $validateData['booking_id'])->update([
            'company_id' => $validateData['company_id'],
            'company_name' => $company->company_name,
        ]);
        return response()->json(['message' =>__('booking.Provider selected')],201);
    }


    public function selectVenue(Request $request, BookingVenueCheck $check, AddPriceAndCreatBookingVenue $creatBookingVenue): JsonResponse
    {
        $validatedData = $request->validate([
            'booking_id' => ['required','exists:bookings,id',new CheckUserBooking()],
            'venue_id' => ['required','exists:venues,id',new CheckCompanyVenue($request->booking_id)],
        ]);

        $booking = Booking::find($validatedData['booking_id']);

        if ($booking->venue()->exists()) {
            return response()->json(['message' =>__('booking.Venue already Selected')], 409);
        }

        $checkVenue = $check->checkAvailability($validatedData['venue_id'], $booking->start_time, $booking->end_time, $booking->booking_date);

        if (!$checkVenue) {
            return response()->json(['message' =>__('booking.Venue not available')], 409);
        }

        $creatBookingVenue($booking, $validatedData);

        return response()->json(['message' =>__('booking.Venue selected')], 201);
    }


    public function selectService(Request $request, BookingServiceCheck $check): JsonResponse
    {

        $validateData = $request->validate([
            'booking_id' => ['required','exists:bookings,id',new CheckUserBooking()],
            'service_id' => ['required','exists:services,id',new CheckCompanyService($request->booking_id)],
            'service_quantity' => ['required', 'integer', 'min:1', new ServiceQuantityAvailable($request->service_id)],
        ]);

        $booking = Booking::find($validateData['booking_id']);

        if ($booking->bookingServices()->where('service_id', $validateData['service_id'])->exists()) {
            return response()->json(['message' =>__('booking.Service already selected')], 409);
        }

        $checkService = $check->checkAvailability($validateData['service_id'], $booking->start_time, $booking->end_time, $validateData['service_quantity'], $booking->booking_date);

        if (!$checkService) {
            return response()->json(['message' =>__('booking.Service not available')], 409);
        }

        $total_price = (new CalculatePriceAndCreateBookingService)($booking, $validateData);
        $newTotalPrice = $booking->total_price + $total_price;
        $booking->update([
            'total_price' => $newTotalPrice,
        ]);

        return response()->json(['message' =>__('booking.Service selected')], 201);
    }


    public function deleteServiceBooking(Request $request): JsonResponse
    {
        $validateData = $request->validate([
            'booking_id' => ['required','exists:bookings,id',new CheckUserBooking()],
            'service_id' => 'required|exists:booking_services,id',
        ]);

        $bookingService = BookingService::where('booking_id', $validateData['booking_id'])->where('service_id', $validateData['service_id'])->first();
        $booking = Booking::find($validateData['booking_id']);
        $updatePrice = $booking->total_price - $bookingService->service_price;
        $booking->update([
            'total_price' => $updatePrice,
        ]);

        $bookingService->delete();

        return response()->json(['message' =>__('booking.Deleted successfully')], 200);
    }


    public function updateQuantityService(Request $request, BookingServiceCheck $check): JsonResponse
    {
        $validateData = $request->validate([
            'booking_id' => ['required','exists:bookings,id',new CheckUserBooking()],
            'service_id' => 'required|exists:services,id',
            'service_quantity' => ['required', 'integer', 'min:1', new ServiceQuantityAvailable($request->input('service_id'))],
        ]);

        $booking = Booking::find($validateData['booking_id']);
        $service = Service::where('id', $validateData['service_id'])->first();
        $serviceBooking = $booking->bookingServices()
            ->where('service_id', $validateData['service_id'])
            ->first();
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

            return response()->json(['message' =>__('booking.Quantity Updated')], 200);
        } elseif ($serviceBooking->service_quantity < $validateData['service_quantity']) {
            $checkService = $check->checkAvailability($validateData['service_id'], $booking->start_time, $booking->end_time, $validateData['service_quantity'], $booking->booking_date, $validateData['service_id']);

            if (!$checkService) {
                return response()->json(['message' =>__('booking.Service not available')], 409);
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

            return response()->json(['message' =>__('booking.Quantity Updated')], 200);
        }

        return response()->json(['message' =>__('booking.Quantity is already the same, no changes applied')], 200);
    }


    public function confirmBooking(Request $request): JsonResponse
{
    $validatedData = $request->validate([
        'booking_id' => ['required','exists:bookings,id',new CheckUserBooking()],
    ]);

    $booking = Booking::find($validatedData['booking_id']);

    if (is_null($booking->status)) {
        if ($booking->user_id === Auth::id()) {
            $booking->update(['status' => 'waiting']);


            if ($booking->company_id) {
                $company = Company::find($booking->company_id);
                $provider = $company->user;

                if ($provider) {
                    $tokens = DeviceToken::where('user_id', $provider->id)
                        ->where('is_active', true)
                        ->pluck('token')
                        ->toArray();

                    $title = 'New Booking Confirmed';
                    $body = Auth::user()->name . ' has confirmed a booking.';

                    if (count($tokens)) {
                        $firebaseService = new FirebaseNotificationService();
                        $firebaseService->sendToTokens($tokens, $title, $body, [
                            'click_action' => 'BOOKING_VIEW',
                            'booking_id' => $booking->id,
                        ]);
                    }


                    Notify::create([
                    'user_id' => $provider->id,
                    'title' => $title,
                    'body' => $body,
                 'data' => ['booking_id' => $booking->id ], ]);

                }
            }
             $user = Auth::user();

            $userTokens = DeviceToken::where('user_id', $user->id)
                ->where('is_active', true)
                ->pluck('token')
                ->toArray();

            $titleToUser = 'Booking Confirmed';
            $bodyToUser = 'Your booking has been successfully confirmed.';

            if (count($userTokens)) {
                $firebaseService->sendToTokens($userTokens, $titleToUser, $bodyToUser, [
                    'click_action' => 'BOOKING_DETAILS',
                    'booking_id' => $booking->id,
                ]);
                
            }

       Notify::create([
    'user_id' => $user->id,
    'title' =>'Booking Confirmed',
    'body' =>'Your booking has been successfully confirmed.',
    'data' => [
        'booking_id' => $booking->id ]]);


            return response()->json(['message' => __('booking.Booking is waiting')], 201);
        }
    }

    return response()->json(['message' => __('booking.Booking is Waiting')], 409);
}


    public function deleteVenue(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'booking_id' => ['required','exists:bookings,id', new CheckUserBooking()],
        ]);

        $booking = Booking::findOrFail($validatedData['booking_id']);
        $venue = $booking->venue;

        $booking->update([
            'total_price' => $booking->total_price - $venue->venue_price,
        ]);

        $venue->delete();

        return response()->json(['message' =>__('booking.Venue Deleted')], 200);
    }


   public function cancelBooking(Request $request): JsonResponse
{
    $validatedData = $request->validate([
        'booking_id' => ['required','exists:bookings,id', new CheckUserBooking(false)],
    ]);

    $booking = Booking::findOrFail($validatedData['booking_id']);

    $company = $booking->company;
    if ($company) {
        $provider = $company->user;

        if ($provider) {
            $user = auth()->user();
            $title = 'Booking Cancelled';
            $body = "{$user->name} has cancelled their booking.";


            $tokens = DeviceToken::where('user_id', $provider->id)
                ->where('is_active', true)
                ->pluck('token')
                ->toArray();

            if (count($tokens)) {
                $firebaseService = new FirebaseNotificationService();
                $firebaseService->sendToTokens($tokens, $title, $body, [
                    'click_action' => 'BOOKING_CANCELLED',
                    'booking_id' => $booking->id,
                ]);
            }
Notify::create([
    'user_id' => $provider->id,
    'title' => $title,
    'body' =>$body,
    'data' => ['booking_id' => $booking->id],
]);


        }
    }
     $titleToUser = 'Booking Cancelled';
    $bodyToUser = 'You have successfully cancelled your booking.';

    $userTokens = DeviceToken::where('user_id', $user->id)
        ->where('is_active', true)
        ->pluck('token')
        ->toArray();

    if (count($userTokens)) {
        $firebaseService->sendToTokens($userTokens, $titleToUser, $bodyToUser, [
            'click_action' => 'BOOKING_CANCELLED',
            'booking_id' => $booking->id,
        ]);
    }
   Notify::create([
    'user_id' => $user->id,
    'title' => 'Booking Cancelled',
    'body' => 'You cancelled your booking.',
    'data' => ['booking_id' => $booking->id],
]);




    $booking->delete();

    return response()->json(['message' => __('booking.Booking Deleted')], 200);
}

    public function addLocation(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'booking_id' => ['required','exists:bookings,id', new CheckUserBooking()],
            'location'=>['required','string'],
        ]);
        $booking = Booking::find($validatedData['booking_id']);
        if ($booking->venue()->exists()) {
            return response()->json(['message' =>'you cant do this youu  are added venue'], 409);
        }
        $booking->location = $validatedData['location'];
        $booking->save();
        return response()->json(['message' => 'booking location Added'], 200);
    }
    public function updateLocation(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'booking_id' => ['required','exists:bookings,id', new CheckUserBooking()],
            'location'=>['required','string'],
        ]);
        $booking = Booking::find($validatedData['booking_id']);
        if (!empty($booking->location)) {
            $booking->update([
                'location' => $validatedData['location'],
            ]);
            return response()->json(['message' => 'Booking location updated'], 200);
        }
        return response()->json(['message' => 'Booking location not found or empty'], 404);
    }

    public function deleteLocation(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'booking_id' => ['required','exists:bookings,id', new CheckUserBooking()],
        ]);

        $booking = Booking::find($validatedData['booking_id']);

        if (!empty($booking->location)) {
            $booking->update(['location' => null]);
            return response()->json(['message' => 'Booking location deleted'], 200);
        }

        return response()->json(['message' => 'Booking location not found'], 200);
    }

}
