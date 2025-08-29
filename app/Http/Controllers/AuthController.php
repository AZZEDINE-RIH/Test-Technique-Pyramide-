<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request) {
        // Force JSON response regardless of Accept header
        $request->headers->set('Accept', 'application/json');

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        $token = $user->createToken('api_token')->plainTextToken;

        // Debug information
        Log::info('User registered', ['user_id' => $user->id, 'token_created' => !empty($token)]);

        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    public function login(Request $request) {
        // Force JSON response regardless of Accept header
        $request->headers->set('Accept', 'application/json');

        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('api_token')->plainTextToken;
        
        // Debug information
        \Log::info('User logged in', ['user_id' => $user->id, 'token_created' => !empty($token)]);

        return response()->json(['user' => $user, 'token' => $token], 200);
    }

    public function logout(Request $request) {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out'], 200);
    }

    public function user(Request $request) {
        return response()->json($request->user());
    }
}
