<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $user = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('status', 'Google authentication failed.');
        }

        $googleUser = User::where('google_id', $user->getId())
            ->orWhere('email', $user->getEmail())
            ->first();

        if ($googleUser) {
            $googleUser->update(['google_id' => $user->getId()]);
        } else {
            $googleUser = User::create([
                'google_id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'role_id' => Role::where('name', 'customer')->first()->id,
                'password' => Hash::make(Str::random(24)),
                'email_verified_at' => now(),
            ]);
        }

        if (!$googleUser->hasVerifiedEmail()) {
            $googleUser->markEmailAsVerified();
        }

        Auth::login($googleUser);

        return redirect()->route('dashboard');
    }
}
