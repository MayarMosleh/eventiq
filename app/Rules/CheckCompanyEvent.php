<?php

namespace App\Rules;

use App\Models\Booking;
use App\Models\CompanyEvent;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckCompanyEvent implements ValidationRule
{
    private $booking_id;

    public function __construct($booking_id)
    {
        $this->booking_id = $booking_id;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $booking = Booking::find($this->booking_id);

        if (!$booking) {
            $fail("Booking not found.");
            return;
        }

        $event_id = $booking->event_id;

        if (!CompanyEvent::where('event_id', $event_id)
            ->where('company_id', $value)
            ->exists()) {

            $fail("This event does not belong to your company.");
        }
    }
}
