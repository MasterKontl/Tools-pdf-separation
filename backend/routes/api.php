<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminPlanController;
use App\Http\Controllers\Admin\AdminSubscriptionController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PdfConverterController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\SeparationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tools DKV REST API Routes
|--------------------------------------------------------------------------
| Single source of truth: All API endpoints delegate directly to the unified
| controllers and service layer. Zero logic duplication.
*/

// ==========================================
// Health & System Information
// ==========================================
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'Tools DKV API',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// ==========================================
// Public & Authentication Endpoints
// ==========================================
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

// ==========================================
// Plans & Public Payments
// ==========================================
Route::get('/plans', [PricingController::class, 'index']);
Route::post('/payments/webhook/pakasir', [PaymentController::class, 'webhook']);
Route::post('/payment/webhook/pakasir', [PaymentController::class, 'webhook']);
Route::get('/payments/status/{orderId}', [PaymentController::class, 'status']);
Route::get('/payment/status/{orderId}', [PaymentController::class, 'status']);
Route::get('/payment/finish/{orderId}', [PaymentController::class, 'finish']);

// ==========================================
// PDF Converter & Quota (Optional Auth: Guest or User)
// ==========================================
Route::middleware('auth.api.optional')->group(function () {
    Route::get('/quota', [PdfConverterController::class, 'quota']);
    Route::get('/converter/status', [PdfConverterController::class, 'quota']);
    Route::post('/convert', [PdfConverterController::class, 'convert']);
    Route::post('/converter/convert', [PdfConverterController::class, 'convert']);
    Route::post('/convert/fetch-url', [PdfConverterController::class, 'fetchUrl']);
    Route::post('/converter/fetch-url', [PdfConverterController::class, 'fetchUrl']);

    // Separation engine endpoints
    Route::get('/separation/config', [SeparationController::class, 'index']);
    Route::get('/separation', [SeparationController::class, 'index']);
    Route::post('/separation', [SeparationController::class, 'process']);
    Route::post('/separation/process', [SeparationController::class, 'process']);
    Route::get('/separation/manifest/{token}', [SeparationController::class, 'manifest']);
    Route::get('/separation/preview/{token}/{channel}', [SeparationController::class, 'preview']);
    Route::get('/separation/download/{token}/{channel?}', [SeparationController::class, 'download']);
});

// ==========================================
// Authenticated User Endpoints
// ==========================================
Route::middleware('auth.api')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::post('/pricing/checkout/{plan}', [PricingController::class, 'checkout']);
    Route::post('/payments/checkout/{plan}', [PricingController::class, 'checkout']);
});

// ==========================================
// Administrator Endpoints
// ==========================================
Route::middleware(['auth.api', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);

    // User Management
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::get('/users/{user}', [AdminUserController::class, 'edit']);
    Route::match(['put', 'patch'], '/users/{user}', [AdminUserController::class, 'update']);
    Route::post('/users/{user}/toggle-unlimited', [AdminUserController::class, 'toggleUnlimited']);
    Route::post('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive']);

    // Plan Management
    Route::get('/plans', [AdminPlanController::class, 'index']);
    Route::post('/plans', [AdminPlanController::class, 'store']);
    Route::get('/plans/{plan}', [AdminPlanController::class, 'edit']);
    Route::match(['put', 'patch'], '/plans/{plan}', [AdminPlanController::class, 'update']);
    Route::post('/plans/{plan}/toggle-active', [AdminPlanController::class, 'toggleActive']);

    // Transactions & Subscriptions
    Route::get('/payments', [AdminPaymentController::class, 'index']);
    Route::get('/subscriptions', [AdminSubscriptionController::class, 'index']);
});
