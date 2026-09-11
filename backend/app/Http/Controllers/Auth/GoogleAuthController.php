<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use App\Services\SecurityAuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the Google OAuth callback.
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            report($e);
            return redirect()->route('login')->with('error', 'Gagal mengotentikasi dengan Google. Silakan coba lagi.');
        }

        $googleId = $googleUser->getId();
        $email = $googleUser->getEmail();
        $name = $googleUser->getName() ?: $email;
        $avatar = $googleUser->getAvatar();

        if (!$email) {
            return redirect()->route('login')->with('error', 'Tidak dapat mengambil email dari akun Google.');
        }

        // A. Check if Google ID already linked to a user
        $user = User::where('google_id', $googleId)->first();

        if ($user) {
            return $this->loginUser($user, request(), 'google_id_match');
        }

        // B. Check if email matches existing user (link Google ID)
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->update([
                'google_id' => $googleId,
                'avatar' => $avatar,
                'provider' => 'google',
            ]);

            SecurityAuditLogger::oauthAccountLinked(request(), $user, 'google');

            return $this->loginUser($user, request(), 'email_match_linked');
        }

        // C. Create new user
        $freePlan = Plan::where('slug', 'free')->first();

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => null,
            'google_id' => $googleId,
            'avatar' => $avatar,
            'provider' => 'google',
            'email_verified_at' => now(),
            'role' => 'USER',
            'plan_id' => $freePlan?->id,
            'daily_limit' => null,
            'unlimited' => false,
            'is_active' => true,
        ]);

        SecurityAuditLogger::oauthNewUser(request(), $user, 'google');

        return $this->loginUser($user, request(), 'new_user');
    }

    /**
     * Log in the given user and redirect to dashboard.
     */
    private function loginUser(User $user, $request, string $context): RedirectResponse
    {
        if (!$user->is_active) {
            return redirect()->route('login')->with('error', 'Akun Anda sedang dinonaktifkan. Silakan hubungi Administrator.');
        }

        Auth::login($user);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        SecurityAuditLogger::loginSuccess($request, $user);

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('dashboard'));
    }
}
