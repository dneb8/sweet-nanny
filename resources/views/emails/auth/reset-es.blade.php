@component('mail::message')
<div style="text-align: center; margin-bottom: 30px;">
<img src="{{ asset('images/Logo-SweetNanny-Claro.svg') }}" alt="Sweet Nanny" style="max-width: 200px; height: auto;">
</div>

# ¡Hola, {{ $user->name }}!

Has recibido este correo porque solicitaste restablecer tu contraseña en Sweet Nanny.

Para crear una nueva contraseña, haz clic en el siguiente botón:

@component('mail::button', ['url' => $url])
Restablecer contraseña
@endcomponent

Este enlace de restablecimiento expirará en 60 minutos.

Si no solicitaste restablecer tu contraseña, puedes ignorar este mensaje de forma segura. Tu contraseña no será modificada.

Saludos,<br>
{{ config('app.name') }}

---

Si tienes problemas al hacer clic en el botón "Restablecer contraseña", copia y pega la siguiente URL en tu navegador:

{{ $url }}
@endcomponent
