<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Jobs\sendWelcome;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
}
