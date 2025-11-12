<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;
use App\Models\BookingAppointment;
use App\Observers\BookingAppointmentObserver;
use Illuminate\Support\Facades\URL;
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
        if (config('app.env') === 'production') {
        URL::forceScheme('https');
    }
        // Customize verification email in Spanish with CID logo
        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            $mail = (new MailMessage)
                ->subject('Verifica tu correo')
                ->markdown('emails.auth.verify-es', ['url' => $url, 'user' => $notifiable]);

            // Embed logo using CID
            $mail->withSymfonyMessage(function (\Symfony\Component\Mime\Email $email) {
                $logoPath = public_path('images/logo-email.png');
                if (file_exists($logoPath)) {
                    $email->embedFromPath($logoPath, 'logo_cid');
                }
            });

            return $mail;
        });

        // Customize password reset email in Spanish with CID logo
        ResetPassword::toMailUsing(function ($notifiable, $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            $mail = (new MailMessage)
                ->subject('Restablece tu contraseña')
                ->markdown('emails.auth.reset-es', ['url' => $url, 'user' => $notifiable]);

            // Embed logo using CID
            $mail->withSymfonyMessage(function (\Symfony\Component\Mime\Email $email) {
                $logoPath = public_path('images/logo-email.png');
                if (file_exists($logoPath)) {
                    $email->embedFromPath($logoPath, 'logo_cid');
                }
            });

            return $mail;
        });

        
        //Para STATUS
        BookingAppointment::observe(BookingAppointmentObserver::class);
    }
}
