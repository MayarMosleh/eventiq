<?php

namespace App\Jobs;

use App\Services\StripeServices;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class TransferToProviderJob implements ShouldQueue
{
    use Queueable;
    protected $providerStripeAccountId;
    protected $amount;
    protected $bookingId;

    public $tries = 5;
    /**
     * Create a new job instance.
     */
    public function __construct(string $providerStripeAccountId , float $amount, string $bookingId)
    {
        $this->providerStripeAccountId = $providerStripeAccountId;
        $this->amount = $amount;
        $this->bookingId = $bookingId;
    }

    /**
     * Execute the job.
     * @throws \Exception
     */
    public function handle(StripeServices $stripeService): void
    {
        try {
            $stripeService->transferToProvider(
                $this->providerStripeAccountId,
                $this->amount,
                $this->bookingId);
        }
        catch (\Exception $e) {
            Log::error('Transfer failed : ' . $e->getMessage());
        }
    }
}
