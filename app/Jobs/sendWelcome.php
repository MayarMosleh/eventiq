<?php

namespace App\Jobs;

use App\Mail\WelcomeMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class sendWelcome implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public $users;
    public function __construct($user)
    {
        $this->users = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = $this->users;
        Mail::to($user->email)->send(new WelcomeMail($user));
    }
}
