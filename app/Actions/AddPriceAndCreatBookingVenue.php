<?php

namespace App\Actions;

use App\Models\Booking;
use App\Models\BookingVenue;
use App\Models\venue;
use Carbon\Carbon;

class AddPriceAndCreatBookingVenue
{
         protected $bufferHours = 1;

        public function __invoke($booking,$validateData): void
        {
            $venue = Venue::findOrFail($validateData['venue_id']);

            $startTime = Carbon::parse($booking->start_time)->subHours($this->bufferHours);
            $endTime = Carbon::parse($booking->end_time)->addHours($this->bufferHours);

             BookingVenue::create([
                'booking_id'    => $booking->id,
                'venue_id'      => $venue->id,
                'venue_name'    => $venue->venue_name,
                'venue_address' => $venue->address,
                'venue_price'   => $venue->venue_price,
                'booking_date'  => $booking->booking_date,
                'start_time'    => $startTime,
                'end_time'      => $endTime,
            ]);
            $newTotalPrice = $booking->total_price + $venue->venue_price;
             $booking->update([
                 'total_price' =>$newTotalPrice
             ]);
        }
}
