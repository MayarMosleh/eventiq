<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckUserBooking implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public $flag;
    public function __construct($flag = true)
    {
        $this->flag = $flag;
    }
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = Auth::user();
        try {
            $booking = $user->bookings()->where('id', $value)->first();
        }
        catch (\Exception $e) {
            $fail($e->getMessage());
            return;
        }

        if (!$booking) {
            $fail("This booking does not belong to your account.");
            return;
        }
        if ($this->flag) {
            $status = $booking->status;
            if (!is_null($status)) {
                $fail('This booking status is ' . $status . ' you cant do it this');
            }
        }
    }
}
