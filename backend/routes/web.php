<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminPlanController;
use App\Http\Controllers\Admin\AdminSubscriptionController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PdfConverterController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\SeparationController;
use App\Http\Controllers\UpscalerController;
use Illuminate\Support\Facades\Route;

// ==========================================
// V1 - PDF Converter
// ==========================================
Route::get('/', [PdfConverterController::class, 'index'])->name('converter.index');

Route::middleware('throttle:convert')->group(function () {
    Route::post('/convert', [PdfConverterController::class, 'convert'])->name('converter.process');
    Route::post('/convert/fetch-url', [PdfConverterController::class, 'fetchUrl'])->name('converter.fetch-url');
    Route::post('/convert/start', [PdfConverterController::class, 'start'])->name('converter.start');
});

Route::get('/convert/status/{jobId}', [PdfConverterController::class, 'jobStatus'])->name('converter.job-status');
Route::get('/convert/result/{jobId}', [PdfConverterController::class, 'jobDownload'])->name('converter.job-download');

// ==========================================
// V2.1 - Color Separation
// ==========================================
Route::get('/separation', [SeparationController::class, 'index'])->name('separation.index');

Route::middleware('throttle:convert')->group(function () {
    Route::post('/separation', [SeparationController::class, 'process'])->name('separation.process');
});

Route::get('/separation/preview/{token}/{channel}', [SeparationController::class, 'preview'])->name('separation.preview');
Route::get('/separation/download/{token}/{channel?}', [SeparationController::class, 'download'])->name('separation.download');

// ==========================================
// V3 - Image Upscaler
// ==========================================
Route::get('/upscaler', [UpscalerController::class, 'index'])->name('upscaler.index');

Route::middleware('throttle:convert')->group(function () {
    Route::post('/upscaler/process', [UpscalerController::class, 'process'])->name('upscaler.process');
});

Route::get('/upscaler/download/{tempId}/{ext}', [UpscalerController::class, 'download'])->name('upscaler.download');

// ==========================================
// Pricing & Public Payment
// ==========================================
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing.index');
Route::post('/payment/webhook/pakasir', [PaymentController::class, 'webhook'])->name('payment.webhook');
Route::get('/payment/finish/{orderId}', [PaymentController::class, 'finish'])->name('payment.finish');

// ==========================================
// Guest Authentication
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

    Route::middleware('throttle:register')->group(function () {
        Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
        Route::post('/register', [AuthController::class, 'register'])->name('register.attempt');
    });

    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');

    // Google OAuth
    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
});

// ==========================================
// Authenticated User Routes
// ==========================================
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/pricing/checkout/{plan}', [PricingController::class, 'checkout'])->name('pricing.checkout');
});

// ==========================================
// Administrator Routes
// ==========================================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // User management
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::match(['put', 'patch'], '/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/toggle-unlimited', [AdminUserController::class, 'toggleUnlimited'])->name('users.toggle-unlimited');
    Route::post('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('users.toggle-active');

    // Plan management
    Route::get('/plans', [AdminPlanController::class, 'index'])->name('plans.index');
    Route::get('/plans/create', [AdminPlanController::class, 'create'])->name('plans.create');
    Route::post('/plans', [AdminPlanController::class, 'store'])->name('plans.store');
    Route::get('/plans/{plan}/edit', [AdminPlanController::class, 'edit'])->name('plans.edit');
    Route::match(['put', 'patch'], '/plans/{plan}', [AdminPlanController::class, 'update'])->name('plans.update');
    Route::post('/plans/{plan}/toggle-active', [AdminPlanController::class, 'toggleActive'])->name('plans.toggle-active');

    // Payments & Subscriptions
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::get('/subscriptions', [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');
});

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'Tools DKV API',
        'timestamp' => now()->toIso8601String(),
    ]);
});

Route::get('/robots.txt', function () {
    $content = <<<'TXT'
User-agent: *
Allow: /

Disallow: /admin
Disallow: /dashboard
Disallow: /login
Disallow: /register
Disallow: /forgot-password
Disallow: /reset-password
Disallow: /logout
Disallow: /payment/finish
Disallow: /payment/webhook

Sitemap: https://kurniawansatya.xyz/sitemap.xml
TXT;

    return response($content, 200, ['Content-Type' => 'text/plain']);
})->name('robots.txt');

Route::get('/sitemap.xml', function () {
    $baseUrl = config('app.url');
    $lastModified = now()->toIso8601String();

    $publicRoutes = [
        'converter.index' => ['priority' => '1.0', 'changefreq' => 'weekly'],
        'separation.index' => ['priority' => '0.9', 'changefreq' => 'weekly'],
        'upscaler.index' => ['priority' => '0.9', 'changefreq' => 'weekly'],
        'pricing.index' => ['priority' => '0.8', 'changefreq' => 'monthly'],
    ];

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($publicRoutes as $routeName => $meta) {
        $url = route($routeName, [], false);
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . e($baseUrl . $url) . '</loc>' . "\n";
        $xml .= '    <lastmod>' . $lastModified . '</lastmod>' . "\n";
        $xml .= '    <changefreq>' . $meta['changefreq'] . '</changefreq>' . "\n";
        $xml .= '    <priority>' . $meta['priority'] . '</priority>' . "\n";
        $xml .= '  </url>' . "\n";
    }

    $xml .= '</urlset>';

    return response($xml, 200, ['Content-Type' => 'application/xml']);
})->name('sitemap.xml');

