<?php

namespace App\Rules;

use AllowDynamicProperties;
use App\Models\Service;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

#[AllowDynamicProperties] class ServiceQuantityAvailable implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */


    public function __construct($service_id){
        $this->service_id = $service_id;
    }


    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $service = Service::find($this->service_id);
        if($service->service_quantity < $value){
            $fail("The requested quantity is unavailable.");
        }
    }
}
