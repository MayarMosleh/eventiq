<?php

namespace App\Actions;

use App\Models\BookingService;
use App\Models\Service;
use Carbon\Carbon;

class CalculatePriceAndCreateBookingService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }



    public function __invoke($booking,$validateDate): float
    {

        $service = Service::findOrFail($validateDate['service_id']);
        $price = $service->service_price * $validateDate['service_quantity'];
        $bufferHours = 1;
        BookingService::create([
            'booking_id' => $validateDate['booking_id'],
            'service_id' => $validateDate['service_id'],
            'service_name' => $service->service_name,
            'service_price' => $price,
            'service_description' => $service->service_description,
            'start_time' => Carbon::parse($booking->start_time)->subHours($bufferHours),
            'end_time' => Carbon::parse($booking->end_time)->addHours($bufferHours),
            'service_quantity' => $validateDate['service_quantity'],
            'date' => $booking->booking_date,
        ]);
        return $price;
    }
}
