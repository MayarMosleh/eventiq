<?php

namespace App\Services;

use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Balance;
use Stripe\Charge;
use Stripe\PaymentIntent;
use Stripe\SetupIntent;
use Stripe\Stripe;
use Stripe\Transfer;

class StripeServices
{
    /**
     * @throws \Exception
     */
    public function __construct(){
        $secret_key = env('STRIPE_SECRET');
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
                   'card_payments' => ['requested' => true],
                   'transfers' =>  ['requested' => true],
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
    public function payment(float $amount, string $paymentMethodId,string $booking_id): PaymentIntent
    {
        $amountInCents = (int) round($amount * 100);
        try {
            $payment = PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => 'usd',
                'payment_method' => $paymentMethodId,
                'confirm' => true,
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never',
                ],
                'description' => 'Payment for booking ' . $booking_id,
            ]);
            return $payment;
        } catch (\Exception $e) {
            throw new \Exception("Stripe payment failed: " . $e->getMessage());
        }
    }

    public function transferToProvider(string $providerStripeAccountId, float $amount, string $booking_id): Transfer
    {
        $eightyPercent = $amount * 0.80;
        $amountInCents = (int) round($eightyPercent * 100);

        try {
            $transfer = Transfer::create([
                'amount' => $amountInCents,
                'currency' => 'usd',
                'destination' => $providerStripeAccountId,
                'transfer_group' => 'booking_' . $booking_id,
            ]);
            return $transfer;
        } catch (\Exception $e) {
            throw new \Exception("Stripe transfer failed: " . $e->getMessage());
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
