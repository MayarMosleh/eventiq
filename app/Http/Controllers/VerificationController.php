<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\VerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    protected VerificationService $verifier;

    public function __construct(VerificationService $verifier)
    {
        $this->verifier = $verifier;
    }

    public function send(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->verifier->sendCode($user->email);

        return response()->json(['message' => 'Verification code sent!'], 200);
    }

    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required',
        ]);

        $user = $request->user();


        if ($this->verifier->verifyCode($user->email, $request->code)) {
            if (is_null($user->email_verified_at)) {
                $user->email_verified_at = now();
                $user->save();
            }
            return response()->json(['message' => 'Verified successfully!'], 200);
        }

        return response()->json(['message' => 'Invalid or expired code.'], 422);
    }
}
