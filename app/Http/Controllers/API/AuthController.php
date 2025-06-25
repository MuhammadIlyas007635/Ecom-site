<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
{

    // dd($request->all());
    // $request->validate([
    //     'name' => 'required|string|max:255',
    //     'email' => 'required|string|email|unique:users',
    //     'phone' => 'nullable|',
    //     'address' => 'nullable|',
    //     'password' => 'required|string|min:6|confirmed',
    // ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'phone' => $request->phone,
        'address' => $request->address,
        'password' => Hash::make($request->password),
    ]);

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'access_token' => $token,
        'token_type' => 'Bearer',
        'user' => $user,
    ], 201);
}

     
          public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! \Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'access_token' => $token,
        'token_type' => 'Bearer',
         'message' => 'user login successfully',
        'user' => $user,
    ]);
}

   public function logout(Request $request)
{
    if (!$request->user()) {
        return response()->json(['message' => 'No authenticated user.'], 401);
    }

    $request->user()->tokens()->delete();

    return response()->json(['message' => 'Logged out successfully']);
}
}
