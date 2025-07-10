<?php

namespace App\Rules;

use App\Models\Booking;
use App\Models\Company;
use App\Models\CompanyEvent;
use App\Models\venue;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckCompanyVenue implements ValidationRule
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
            $fail("Booking not found.");
            return;
        }
        $company_id = $booking->company_id;

        if (!Venue::where('id',$value)->
            where('company_id',$company_id)->exists()) {
            $fail("This venue does not belong to your company.");
        }
    }
}
