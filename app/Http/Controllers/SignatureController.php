<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserSignature;
use Illuminate\Support\Facades\Auth;

class SignatureController extends Controller
{
    /**
     * Prikazuje stranicu za potpisivanje
     */
    public function show()
    {
        return view('signature');
    }

    /**
     * Sprema potpis korisnika u bazu
     */
    public function save(Request $request)
    {
        $request->validate([
            'signature' => 'required|string',
        ]);

        $userId = Auth::id();
        $signature = $request->input('signature');

        // Ako korisnik već ima potpis, ažuriraj ga
        UserSignature::updateOrCreate(
            ['user_id' => $userId],
            ['signature' => $signature]
        );

        return response()->json(['message' => 'Potpis uspješno spremljen!']);
    }
}
