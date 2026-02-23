<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6|confirmed', // يحتاج password_confirmation
        ]);

        // ✅ لازم نعبّي users.name لأنه NOT NULL في DB
        $user = User::create([
            'name'     => $data['full_name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // ✅ إنشاء profile عشان RoleMiddleware ما يعلق
        $profile = Profile::create([
            'user_id'   => $user->id,
            'full_name' => $data['full_name'],
            'role'      => 'member',
        ]);

        $token = $user->createToken('mobile-client')->plainTextToken;

        return response()->json([
            'message' => 'Registered successfully',
            'token'   => $token,
            'user'    => $user->load('profile'),
        ], 201);
    }

    /**
     * Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $user->createToken('mobile-client')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => $user->load('profile'),
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }
}
