<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;


class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Failed! Email or password does not match!'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function signup(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'password_confirmation' => 'required|same:password',
        ]);

        $userData = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        return response()->json(['message' => "User added", "userData" => $userData], 200);
    }
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        Log::info('User info:', ['user' => auth()->user()]);
        return response()->json(auth()->user());
    }

    public function changePassword(Request $request)
    {
        $user = auth()->user();
        Log::info('User info pw:', ['user' => auth()->user()]);
        // Validate the incoming request data
        $request->validate([
            'oldPassword' => 'required',
            'newPassword' => 'required|confirmed',
        ]);
        Log::info('old pw:', ['request' => $request->oldPassword]);
        Log::info('User  pw:', ['user' => $user->password]);
        // Check if the old password is correct
        if (!Hash::check($request->oldPassword, $user->password)) {
            return response()->json(['error' => 'The old password is incorrect.'], 400);
        }

        // Update the password
        $user->password = $request->newPassword;
        $user->save();

        return response()->json(['message' => 'Password updated successfully.']);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        // return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            // 'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
