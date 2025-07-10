<?php

namespace App\Jobs;

use App\Mail\VerificationCodeMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendVerificationCodeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $email;
    public $code;
    public $tries = 10;
    public $backoff = 2;
    public function __construct($email, $code)
    {
        $this->email = $email;
        $this->code = $code;
    }

    public function handle(): void
    {
        Mail::to($this->email)->send(new VerificationCodeMail($this->code));
    }

    public function failed(\Throwable $exception): void
    {

        Log::error('Failed to send verification code to ' . $this->email . ' - Error: ' . $exception->getMessage());
    }
}
