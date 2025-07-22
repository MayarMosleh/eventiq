<?php

namespace App\Services;

use App\Jobs\SendVerificationCodeEmail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VerificationService
{
    public function sendCode(string $email): void
    {
        $code = $this->generateCode();

        Cache::put($this->cacheKey($email), Hash::make($code), now()->addMinutes(2));

        SendVerificationCodeEmail::dispatch($email, $code);

        Log::info("Verification code for {$email}: {$code}");
    }


    public function verifyCode(string $email, string $inputCode): bool
    {
        $cachedHash = Cache::get($this->cacheKey($email));

        if (! $cachedHash) {
            return false;
        }

        if (! Hash::check($inputCode, $cachedHash)) {
            return false;
        }

        Cache::forget($this->cacheKey($email));

        return true;
    }


    protected function generateCode(): string
    {
        return (string) rand(100000, 999999);
    }


    protected function cacheKey(string $email): string
    {
        return 'verification_code_' . sha1($email);
    }
}
