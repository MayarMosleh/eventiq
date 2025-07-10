<?php
namespace App\Http\Controllers;
use App\Jobs\TransferToProviderJob;
use App\Models\Booking;
use App\Models\Company;
use App\Models\DeviceToken;
use App\Models\Notify;
use App\Services\FirebaseNotificationService;
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
            $paymentIntent = $this->stripeService->payment($booking->total_price, $validatedData['payment_method_id'], $validatedData['booking_id']);
            $booking->update([
                'status' => 'paid',
            ]);
        // جنى هون كان ناقص ال كومباني منشان اتذكر
            $company = Company::findOrFail($booking->company_id);
            $provider = $company->user;
        $user = auth()->user();
        $title = 'New Payment Received';
        $body = "{$user->name} has paid for booking ID {$booking->id}.";


        $tokens = DeviceToken::where('user_id', $provider->id)
            ->where('is_active', true)
            ->pluck('token')
            ->toArray();

        if (count($tokens)) {
            $firebaseService = new FirebaseNotificationService();
            $firebaseService->sendToTokens($tokens, $title, $body, [
                'click_action' => 'BOOKING_PAYMENT_VIEW',
                'booking_id' => $booking->id,
            ]);
        }

        Notify::insert([
            'user_id' => $provider->id,
            'title' => $title,
            'body' => $body,
            'data' => json_encode([
                'booking_id' => $booking->id,
                'amount' => $booking->total_price,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
            'read_at' => null,
        ]);
            $providerStripeAccountId = $company->user->stripe_account_id;
            if ($paymentIntent->status == 'succeeded') {
            if ($providerStripeAccountId) {
                TransferToProviderJob::dispatch(
                    $providerStripeAccountId,
                    $booking->total_price,
                    $booking->id
                );
            }
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
