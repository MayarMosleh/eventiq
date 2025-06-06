<?php

namespace App\Services;

use App\Models\BookingVenue;
use Carbon\Carbon;

class BookingVenueCheck
{
    protected $bufferHours = 1;

    public function checkAvailability($venueId, $startTime, $endTime, $bookingDate, $excludeBookingVenueId = null): bool
    {
        $startTime = Carbon::parse($startTime)->subHours($this->bufferHours);
        $endTime = Carbon::parse($endTime)->addHours($this->bufferHours);

        $hasConflict = BookingVenue::where('venue_id', $venueId)
            ->where('booking_date', $bookingDate)
            ->when($excludeBookingVenueId, function ($query) use ($excludeBookingVenueId) {
                return $query->where('id', '<>', $excludeBookingVenueId);
            })
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            })
            ->exists();

        return !$hasConflict;
    }
}
