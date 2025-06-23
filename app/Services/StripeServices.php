<?php

namespace App\Services;

use Stripe\Account;
use Stripe\AccountLink;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class StripeServices
{
    /**
     * @throws \Exception
     */
    public function __construct(){
        $secret_key = env('STRIPE_SECRET_KEY');
        if (empty($secret_key)) {
            throw new \Exception("Stripe Secret Key not set");
        }
        Stripe::setApiKey($secret_key);
   }

    /**
     * @throws \Exception
     */
    public function createAccount(string $email , string $country){
       try {
           $stripeAccount = Account::create([
               'type' => 'express',
               'country' => $country,
               'email' => $email,
               'capabilities'=>[
                   'card_payments' =>  true,
                   'transfers' =>  true,
               ]
           ]);
           return $stripeAccount;
       }
       catch (\Exception $e) {
           throw new \Exception("Failed to create Stripe account: " .$e->getMessage());
       }
   }

    /**
     * @throws \Exception
     */
    public function createAccountLink(string $accountId): AccountLink
    {
        try {
            $accountLink =  AccountLink::create([
                'account' => $accountId,
                'refresh_url' => url('/stripe/refresh'),
                'return_url' => url('/stripe/success'),
                'type' => 'account_onboarding',
            ]);
            return $accountLink;
        } catch (\Exception $e) {
            throw new \Exception("Failed to create account link: " . $e->getMessage());
        }
    }

    /**
     * @throws \Exception
     */
    public function payment($stripe_account_id, float $amount,string $paymentMethodId): PaymentIntent
    {
        $amountInCents = (int) round($amount * 100);
        $adminFee = (int) round($amountInCents * 0.30);
        try {
            $payment =  PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => 'usd',
                'payment_method' => $paymentMethodId,
                'confirmation_method' => 'automatic',
                'confirm' => true,
                'application_fee_amount' => $adminFee,
            ], [
                'stripe_account' => $stripe_account_id,
            ]);
            return $payment;}
         catch (\Exception $e) {
            throw new \Exception("Stripe payment failed: " . $e->getMessage());
        }
    }

    /**
     * @throws \Exception
     */
    public function getAccountStatus(string $stripe_account_id): bool
    {
        try {
            $status =  Account::retrieve($stripe_account_id);
            return $status->payouts_enabled;
        } catch (\Exception $e) {
            throw new \Exception("Failed to retrieve account: " . $e->getMessage());
        }
    }
}
