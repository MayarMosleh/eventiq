<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Jobs\sendWelcome;
use App\Mail\WelcomeMail;
use App\Models\User;
use App\Services\VerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function register(StoreUserRequest $request)
    {
        $user=User::create($request->validated());
        $token=$user->createToken('auth_token')->plainTextToken;
        sendWelcome::dispatch($user);
        return response()->json(['message'=>'this account has been created','user'=>$user,'token'=>$token],201);
    }
    public function login(Request $request)
    {
        $request->validate(['email'=>'required|email','password'=>'required']);
      if(!Auth::attempt($request->only('email','password')))
      return response()->json(['message'=>'email or password is invalid'],401);

        $user = Auth::user();
        if ($user->tokens()->count() > 0) {
            return response()->json(['message' => 'User already logged in'], 403);
        }

      $user=User::where('email',$request->email)->firstOrFail();
      $token=$user->createToken('auth_token')->plainTextToken;
      return response()->json(['message'=>'login successfully','user'=>$user,'token'=>$token],201);
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message'=>'logout successfully'],200);
    }
      public function index()
    {
        $user=User::all();
        return response()->json($user,200);
    }
    public function show($id)
    {
        $user=User::find($id);
        return response()->json($user,200);
    }

    public function requestPasswordReset(Request $request,VerificationService $resetPasswordService): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['message' => 'Email not found.'], 404);
        }

        $resetPasswordService->sendCode($request->email);

        return response()->json(['message' => 'Verification code sent to your email.']);
    }

    public function resetPassword(Request $request,VerificationService $resetPasswordService): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        if (! $resetPasswordService->verifyCode($request->email, $request->code)) {
            return response()->json(['message' => 'Invalid or expired verification code.'], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        $user->password = Hash::make($request->password);
        $user->save();
        return response()->json(['message' => 'Password has been reset successfully.']);
    }
}
