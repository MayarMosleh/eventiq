<?php
namespace App\Http\Controllers;
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

    public function pay(Request $request){
        $validatedData = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'payment_method_id' => 'required|string',
        ]);

        $booking = Booking::findOrFail($validatedData['booking_id']);
        $company = Company::findOrFail($booking->company_id);
        try {
            $paymentIntent = $this->stripeService->payment($company->user->stripe_account_id, $booking->total_price, $validatedData['payment_method_id']);
            $booking->status = 'paid';
            $booking->save();
            return response()->json([
                'success' => true,
                'payment_intent' => $paymentIntent,
            ]);
        }

        catch (\Exception $e) {
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

    /**
     * @throws \Exception
     */
    public function getStripeAccountId(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);
        try {
            $booking = Booking::findOrFail($validatedData['booking_id']);
            $company = Company::findOrFail($booking->company_id);
            $stripe_account_id = $company->user->stripe_account_id;
            $client_secret = $this->stripeService->createSetupIntent($stripe_account_id);
            return response()->json(['stripe_account_id' => $stripe_account_id
                , 'client_secret' => $client_secret]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

}
