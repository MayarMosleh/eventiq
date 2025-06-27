<?php
namespace App\Http\Controllers;
use App\Jobs\TransferToProviderJob;
use App\Models\Booking;
use App\Models\Company;
use App\Services\StripeServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Stripe;


class StripeConnectController extends Controller
{
    protected $stripeService;

    public function __construct(StripeServices $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * @throws \Exception
     */

    public function connect(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'country'=>'required|string',
        ]);
        $user = Auth::user();
        $account = $this->stripeService->createAccount($user->email, $validatedData['country']);
        $user->stripe_account_id = $account->id;
        $user->save();
        $accountLink = $this->stripeService->createAccountLink($account->id);
        return response()->json(['url' => $accountLink->url]);
    }

    public function payment(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'payment_method_id' => 'required|string',
        ]);

        $booking = Booking::findOrFail($validatedData['booking_id']);
        if ($booking->status == 'paid')
            return response()->json(['message' => 'Booking already paid']);
        try {
            $paymentIntent = $this->stripeService->payment($booking->total_price, $validatedData['payment_method_id']);
            $booking->status = 'paid';
            $booking->save();
            $company = Company::findOrFail($booking->company_id);
            $providerStripeAccountId = $company->user->stripe_account_id;

            if ($providerStripeAccountId) {
                TransferToProviderJob::dispatch(
                    $providerStripeAccountId,
                    $booking->total_price,
                    $booking->id
                );
            }

            return response()->json([
                'message' => 'Payment completed successfully.',
                'payment_intent' => $paymentIntent,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * @throws \Exception
     */
    public function getAccountStatus(Request $request): JsonResponse
    {
        $user = Auth::user();
        $status = $this->stripeService->getAccountStatus($user->stripe_account_id);
        if ($status)
        return response()->json(['status' =>__('stripe.account is enabled')]);
        else
            return response()->json(['status' =>__('stripe.account is disabled')]);
    }




}
