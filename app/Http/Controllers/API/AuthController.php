<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // Register User
    public function register(Request $request)
    {
        Log::info('Registering new user', $request->all());

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Ensure token is created only if Sanctum is working
        $token = method_exists($user, 'createToken') 
            ? $user->createToken('auth_token')->plainTextToken 
            : null;

        return response()->json([
            'message' => 'User registered successfully',
            'token' => $token,
            'user' => $user
        ], 201);
    }

    // Login User
    public function login(Request $request)
    {
        Log::info('Login attempt for email: ' . $request->email);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            Log::error('Invalid credentials for email: ' . $request->email);
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();

        // Ensure token is created only if Sanctum is working
        if (!method_exists($user, 'createToken')) {
            Log::error('Sanctum is missing. Token creation failed.');
            return response()->json(['error' => 'Authentication failed. Sanctum is missing.'], 500);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        Log::info('User logged in successfully: ' . $user->email);

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user
        ]);
    }

    // Logout User
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->tokens()->delete();
            Log::info('User logged out: ' . $request->user()->email);
            return response()->json(['message' => 'Logged out successfully']);
        }

        return response()->json(['error' => 'No authenticated user found'], 401);
    }

    // Get Authenticated User
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    // Forgot Password
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        Password::sendResetLink($request->only('email'));

        return response()->json(['message' => 'Password reset link sent.']);
    }

    // Reset Password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password reset successfully.'])
            : response()->json(['message' => 'Invalid token or email.'], 400);
    }

    // Send Email Verification Notification
    public function sendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 400);
        }

        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Verification email sent.']);
    }

    // Verify Email
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Invalid verification link.'], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 400);
        }

        $user->markEmailAsVerified();
        return response()->json(['message' => 'Email verified successfully.']);
    }

    // Confirm Password
    public function confirmPassword(Request $request)
    {
        if (!Hash::check($request->password, $request->user()->password)) {
            return response()->json(['message' => 'Password does not match.'], 400);
        }

        return response()->json(['message' => 'Password confirmed.']);
    }

    // Update Password
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $request->user()->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 400);
        }

        $request->user()->update(['password' => Hash::make($request->new_password)]);
        return response()->json(['message' => 'Password updated successfully.']);
    }
}
