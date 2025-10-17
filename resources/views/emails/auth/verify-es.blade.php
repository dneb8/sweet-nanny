@component('mail::message')
<div style="text-align: center; margin-bottom: 30px;">
<img src="cid:logo_cid" alt="Sweet Nanny" class="email-logo">
</div>

# ¡Hola, {{ $user->name }}!

Gracias por registrarte en Sweet Nanny. Para completar tu registro, por favor verifica tu dirección de correo electrónico haciendo clic en el botón de abajo.

@component('mail::button', ['url' => $url])
Verificar mi correo
@endcomponent

Si no creaste una cuenta en Sweet Nanny, puedes ignorar este mensaje.

Este enlace de verificación expirará en 60 minutos.

Saludos,<br>
{{ config('app.name') }}

---

Si tienes problemas al hacer clic en el botón "Verificar mi correo", copia y pega la siguiente URL en tu navegador:

{{ $url }}
@endcomponent
