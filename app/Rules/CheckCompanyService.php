<?php

namespace App\Rules;

use App\Models\Booking;
use App\Models\CompanyEvent;
use App\Models\Event;
use App\Models\Service;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckCompanyService implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    private $booking_id;

    public function __construct($booking_id)
    {
        $this->booking_id = $booking_id;
    }
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        $booking = Booking::find($this->booking_id);
        if (!$booking) {
            $fail('booking not found');
            return;
        }
        $company_id = $booking->company_id;
        $event_id = $booking->event_id;
        $companyEvent = CompanyEvent::where('company_id', $company_id)
            ->where('event_id', $event_id)
            ->first();

        if (!$companyEvent) {
            $fail('company event not found');
            return;
        }

        $company_event_id = $companyEvent->id;

        if (!Service::where('id', $value)->where('company_events_id', $company_event_id)->exists()) {
            $fail('service not found in company');
        }

    }
}
