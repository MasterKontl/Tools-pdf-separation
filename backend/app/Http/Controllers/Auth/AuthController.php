<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an authentication attempt with rate limiting.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => "Terlalu banyak percobaan masuk. Silakan coba lagi dalam {$seconds} detik.",
                ], 429);
            }

            return back()->withErrors([
                'email' => "Terlalu banyak percobaan masuk. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('email');
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($throttleKey);
            if ($request->hasSession()) {
                $request->session()->regenerate();
            }
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                if ($request->hasSession()) {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }

                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi Administrator.',
                    ], 403);
                }

                return redirect()->route('login')->with('error', 'Akun Anda sedang dinonaktifkan. Silakan hubungi Administrator.');
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                $token = \App\Services\AuthTokenService::generateToken($user);
                return response()->json([
                    'success' => true,
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'is_admin' => $user->isAdmin(),
                        'can_high_dpi' => $user->canAccessHighDpi(),
                        'unlimited' => $user->isUnlimited(),
                        'plan_id' => $user->plan_id,
                        'plan' => $user->plan,
                    ],
                ]);
            }

            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($throttleKey);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'error' => 'Email atau kata sandi yang Anda masukkan salah.',
            ], 401);
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Show the registration form.
     */
    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    /**
     * Handle registration of a new user.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar. Silakan gunakan email lain atau masuk.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ]);

        $freePlan = Plan::where('slug', 'free')->first();

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => 'USER',
            'plan_id' => $freePlan?->id,
            'daily_limit' => null, // Inherits plan daily_limit (3)
            'unlimited' => false,
            'is_active' => true,
        ]);

        Auth::login($user);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            $token = \App\Services\AuthTokenService::generateToken($user);
            return response()->json([
                'success' => true,
                'token' => $token,
                'message' => 'Akun berhasil dibuat! Selamat datang di Tools DKV.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'is_admin' => $user->isAdmin(),
                    'can_high_dpi' => $user->canAccessHighDpi(),
                    'unlimited' => $user->isUnlimited(),
                    'plan_id' => $user->plan_id,
                    'plan' => $freePlan,
                ],
            ], 201);
        }

        return redirect()->route('dashboard')->with('success', 'Akun berhasil dibuat! Selamat datang di Tools DKV.');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Anda telah berhasil keluar.',
            ]);
        }

        return redirect()->route('converter.index')->with('status', 'Anda telah berhasil keluar.');
    }

    /**
     * Get the authenticated user's profile and quota info.
     */
    public function me(Request $request, \App\Services\QuotaService $quotaService)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'error' => 'Unauthenticated.'], 401);
        }

        $usageInfo = $quotaService->getUsageInfo($user);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_admin' => $user->isAdmin(),
                'can_high_dpi' => $user->canAccessHighDpi(),
                'unlimited' => $user->isUnlimited(),
                'plan_id' => $user->plan_id,
                'plan' => $user->plan,
            ],
            'usageInfo' => $usageInfo,
        ]);
    }
}
