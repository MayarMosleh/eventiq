<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Services\StripeServices;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckStripeAccount
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return Response
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next): Response
    {
        $company = Company::findOrFail($request->company_id);

        $stripeService = app(StripeServices::class);

        $account = $stripeService->getAccountStatus($company->user->stripe_account_id);

        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => 'Stripe account is not fully activated. Please complete your account setup.',
            ], 403);
        }

        return $next($request);
    }
}
