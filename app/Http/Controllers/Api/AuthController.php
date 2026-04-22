<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $loginUserData = $request->validate([
                'email' => 'required|string',
                'password' => 'required'
            ]);

            $user = User::where('email', $loginUserData['email'])->first();

            if (!$user) {
                return response()->json([
                    'message' => 'Invalid email'
                ], 401);
            }

            if (!Hash::check($loginUserData['password'], $user->password)) {
                return response()->json([
                    'message' => 'Wrong Password'
                ], 401);
            }

            $token = $user->createToken($user->name . '-AuthToken')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);
        } catch (Exception $ex) {
            return response()->json([
                'message' => $ex->getMessage()
            ], 401);
        }
    }

    public function logout()
    {

        auth()->user()->tokens()->delete();

        return response()->json([
            "message" => "logged out"
        ]);
    }
}
