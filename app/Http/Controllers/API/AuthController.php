<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            $response = [
                'status' => false,
                'msg' => 'Required field can\'t be empty',
                'error_code' => true,
                'error_message' => $validator->errors()
            ];
            return response()->json($response, 200);
        }

        $credentials = request(['email', 'password']);
        if (!Auth::attempt($credentials)) {
            $response = [
                'status' => false,
                'msg' => 'Unauthorized'
            ];
            return response()->json($response, 200);
        }

        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('auth-token')->plainTextToken;
        $response = [
            'status' => true,
            'user' => $user,
            'token' => $token
        ];
        return response()->json($response, 200);
    }

    public function logout(Request $request)
    {
        if ($request->user()->currentAccessToken()->delete()) {
            $response = [
                'status' => true,
                'msg' => 'Token Deleted'
            ];
            return response()->json($response, 200);
        } else {
            $response = [
                'status' => false,
                'msg' => 'Something Went Wrong!'
            ];
            return response()->json($response, 200);
        }
    }
}
