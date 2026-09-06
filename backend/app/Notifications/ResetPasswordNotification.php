<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    use Queueable;

    /**
     * Build the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);
        $appName = config('app.name', 'Tools DKV');
        $expire = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

        return (new MailMessage)
            ->subject("Atur Ulang Kata Sandi - {$appName}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Anda menerima email ini karena kami menerima permintaan pengaturan ulang kata sandi untuk akun Anda di {$appName}.")
            ->action('Atur Ulang Kata Sandi', $url)
            ->line("Tautan pengaturan ulang kata sandi ini hanya berlaku selama {$expire} menit.")
            ->line('Jika Anda tidak merasa meminta pengaturan ulang kata sandi, abaikan email ini dan kata sandi Anda tetap aman.')
            ->salutation("Salam hangat,\nTim {$appName}");
    }

    /**
     * Get the reset URL for the given notifiable.
     */
    protected function resetUrl(mixed $notifiable): string
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable, $this->token);
        }

        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }
}
