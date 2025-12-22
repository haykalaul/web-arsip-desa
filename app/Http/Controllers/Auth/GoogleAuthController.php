<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Check if user exists with this email
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Create new user if not exists
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => Hash::make(uniqid()), // Random password since OAuth
                    'role' => Role::ADMIN->status(), // Set as admin
                    'is_active' => true,
                    'profile_picture' => $googleUser->getAvatar(),
                    'google_id' => $googleUser->getId(),
                ]);
            } else {
                // Update profile picture and google_id if user exists
                $user->update([
                    'profile_picture' => $googleUser->getAvatar(),
                    'google_id' => $googleUser->getId(),
                ]);
            }

            // Login the user
            Auth::login($user);

            return redirect()->intended('/dashboard');
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['error' => 'Google login failed.']);
        }
    }
}
