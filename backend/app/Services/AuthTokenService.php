<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Crypt;

class AuthTokenService
{
    /**
     * Generate an encrypted API token for the user.
     */
    public static function generateToken(User $user, int $daysValid = 30): string
    {
        $payload = [
            'sub' => $user->id,
            'email' => $user->email,
            'exp' => now()->addDays($daysValid)->timestamp,
            'iat' => now()->timestamp,
        ];

        return Crypt::encryptString(json_encode($payload));
    }

    /**
     * Authenticate and resolve a user from an API token.
     */
    public static function resolveUser(?string $token): ?User
    {
        if (!$token || !is_string($token)) {
            return null;
        }

        try {
            $json = Crypt::decryptString($token);
            $payload = json_decode($json, true);

            if (!is_array($payload) || !isset($payload['sub'], $payload['exp'])) {
                return null;
            }

            if (time() > $payload['exp']) {
                return null;
            }

            $user = User::find($payload['sub']);
            if (!$user || !$user->is_active) {
                return null;
            }

            return $user;
        } catch (Exception) {
            return null;
        }
    }
}
