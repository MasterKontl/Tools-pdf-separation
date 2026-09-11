<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SecurityAuditLogger
{
    /**
     * Log a security-relevant event with structured metadata.
     */
    public static function log(string $event, array $context = [], ?Request $request = null): void
    {
        $payload = [
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
            ...$context,
        ];

        if ($request) {
            $payload['ip'] = $request->ip();
            $payload['user_agent'] = $request->userAgent();
            $payload['route'] = $request->route()?->getName();
            if ($request->user()) {
                $payload['user_id'] = $request->user()->id;
                $payload['user_email'] = $request->user()->email;
            }
        }

        Log::warning('SECURITY_AUDIT: ' . $event, $payload);
    }

    public static function loginSuccess(Request $request, $user): void
    {
        self::log('auth.login.success', ['user_id' => $user->id], $request);
    }

    public static function loginFailed(Request $request, string $email): void
    {
        self::log('auth.login.failed', ['email' => $email], $request);
    }

    public static function loginThrottled(Request $request, string $email): void
    {
        self::log('auth.login.throttled', ['email' => $email], $request);
    }

    public static function accountDeactivated(Request $request, $user): void
    {
        self::log('auth.login.deactivated_account', ['user_id' => $user->id], $request);
    }

    public static function logout(Request $request): void
    {
        self::log('auth.logout', [], $request);
    }

    public static function passwordResetRequested(Request $request, string $email, bool $userExists): void
    {
        self::log('auth.password_reset.requested', ['email' => $email, 'user_exists' => $userExists], $request);
    }

    public static function passwordResetThrottled(Request $request, string $email): void
    {
        self::log('auth.password_reset.throttled', ['email' => $email], $request);
    }

    public static function passwordResetCompleted(Request $request, $user): void
    {
        self::log('auth.password_reset.completed', ['user_id' => $user->id], $request);
    }

    public static function adminAccessDenied(Request $request): void
    {
        self::log('admin.access_denied', [], $request);
    }

    public static function adminRoleChanged(Request $request, int $targetUserId, string $oldRole, string $newRole): void
    {
        self::log('admin.user.role_changed', [
            'target_user_id' => $targetUserId,
            'old_role' => $oldRole,
            'new_role' => $newRole,
        ], $request);
    }

    public static function adminUserToggled(Request $request, int $targetUserId, string $field, $oldValue, $newValue): void
    {
        self::log('admin.user.toggled', [
            'target_user_id' => $targetUserId,
            'field' => $field,
            'old_value' => $oldValue,
            'new_value' => $newValue,
        ], $request);
    }

    public static function paymentWebhookReceived(Request $request, string $orderId): void
    {
        self::log('payment.webhook.received', ['order_id' => $orderId], $request);
    }

    public static function paymentWebhookVerificationFailed(Request $request, string $orderId, string $reason): void
    {
        self::log('payment.webhook.verification_failed', [
            'order_id' => $orderId,
            'reason' => $reason,
        ], $request);
    }

    public static function paymentStatusAccessDenied(Request $request, string $orderId): void
    {
        self::log('payment.status.access_denied', ['order_id' => $orderId], $request);
    }

    public static function quotaExceeded(Request $request, int $used, int $limit): void
    {
        self::log('quota.exceeded', [
            'used' => $used,
            'limit' => $limit,
        ], $request);
    }

    public static function oauthNewUser(Request $request, $user, string $provider): void
    {
        self::log('auth.oauth.new_user', [
            'user_id' => $user->id,
            'provider' => $provider,
        ], $request);
    }

    public static function oauthAccountLinked(Request $request, $user, string $provider): void
    {
        self::log('auth.oauth.linked', [
            'user_id' => $user->id,
            'provider' => $provider,
        ], $request);
    }
}
