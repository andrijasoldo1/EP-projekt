<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserSignature;
use Illuminate\Support\Facades\Auth;

class SignatureController extends Controller
{
    /**
     * Retrieve the authenticated user's signature.
     */
    public function show()
    {
        $userId = Auth::id();
        $signature = UserSignature::where('user_id', $userId)->first();

        if (!$signature) {
            return response()->json(['message' => 'Signature not found.'], 404);
        }

        return response()->json([
            'message' => 'Signature retrieved successfully.',
            'signature' => $signature->signature,
        ]);
    }

    /**
     * Store or update the user's signature.
     */
    public function save(Request $request)
    {
        $request->validate([
            'signature' => 'required|string',
        ]);

        $userId = Auth::id();
        $signature = $request->input('signature');

        // Create or update the signature
        UserSignature::updateOrCreate(
            ['user_id' => $userId],
            ['signature' => $signature]
        );

        return response()->json(['message' => 'Signature saved successfully!']);
    }
}
