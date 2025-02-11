<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user's profile.
     */
    public function show(Request $request)
    {
        try {
            // Authenticate the user manually
            $user = $this->authenticateUser($request);
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            return response()->json([
                'message' => 'User profile retrieved successfully',
                'user' => $user
            ]);
        } catch (\Throwable $e) {
            Log::error("Profile Show Error: " . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(Request $request)
    {
        try {
            // Authenticate user
            $user = $this->authenticateUser($request);
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $validatedData = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
                'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            ]);

            $user->name = $validatedData['name'];
            $user->email = $validatedData['email'];

            // If password is provided, update it
            if (!empty($validatedData['password'])) {
                $user->password = Hash::make($validatedData['password']);
            }

            // If email is changed, mark it as unverified
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => $user
            ]);
        } catch (\Throwable $e) {
            Log::error("Profile Update Error: " . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * Delete the authenticated user's account.
     */
    public function destroy(Request $request)
    {
        try {
            // Authenticate user
            $user = $this->authenticateUser($request);
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $request->validate([
                'password' => ['required'],
            ]);

            if (!Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Incorrect password'], 403);
            }

            // Revoke all tokens before deleting the user
            $user->tokens()->delete();

            // Delete user
            $user->delete();

            return response()->json(['message' => 'Account deleted successfully']);
        } catch (\Throwable $e) {
            Log::error("Profile Delete Error: " . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * Helper function to authenticate user manually using Sanctum token.
     */
    private function authenticateUser(Request $request)
    {
        $token = $request->bearerToken();
        if ($token) {
            $personalAccessToken = PersonalAccessToken::findToken($token);
            if ($personalAccessToken) {
                $user = $personalAccessToken->tokenable;
                Auth::setUser($user);
                return $user;
            }
        }
        return null;
    }
}
