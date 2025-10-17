<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;
use Tightenco\Ziggy\Ziggy;  // si usas Ziggy para rutas en frontend

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Customize verification email in Spanish
        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            return (new MailMessage)
                ->subject('Verifica tu correo')
                ->markdown('emails.auth.verify-es', ['url' => $url, 'user' => $notifiable]);
        });

        // Customize password reset email in Spanish
        ResetPassword::toMailUsing(function ($notifiable, $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject('Restablece tu contraseña')
                ->markdown('emails.auth.reset-es', ['url' => $url, 'user' => $notifiable]);
        });
    }
}
