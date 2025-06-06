<?php

namespace App\Services;

use App\Models\BookingService;
use App\Models\Service;
use Carbon\Carbon;

class BookingServiceCheck
{
    protected $bufferHours = 1;

    public function checkAvailability($serviceId, $startTime, $endTime, $requestQuantity,$booking_date, $excludeBookingServiceId = null): bool
    {
        $startTime = Carbon::parse($startTime)->subHours($this->bufferHours);
        $endTime = Carbon::parse($endTime)->addHours($this->bufferHours);

        $totalBookedQuantity = BookingService::where('service_id', $serviceId)->where('date',$booking_date)
            ->when($excludeBookingServiceId, function ($query) use ($excludeBookingServiceId) {
                return $query->where('id', '<>', $excludeBookingServiceId);
            })
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            })
            ->sum('service_quantity');

        $service = Service::find($serviceId);
        $availableQuantity = $service ? $service->service_quantity : 0;

        return ($availableQuantity - $totalBookedQuantity) >= $requestQuantity;
    }

}
