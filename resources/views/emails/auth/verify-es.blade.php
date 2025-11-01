@component('mail::message')
@php
  // Colores derivados de tu tema
  $primary = '#d99b9b';     // hsl(0,41%,72%) -> HEX
  $textOnPrimary = '#1F2937'; // mejor contraste que blanco
  $beige = '#F7F3EF';       // gris/arena cute de fondo
@endphp

{{-- Barra superior con logo dentro --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin:0;padding:0;background: {{ $primary }};">
  <tr>
    <td align="center" style="padding: 16px 12px;">
      <img src="cid:logo_cid" alt="Sweet Nanny" style="height: 40px; display:inline-block;">
    </td>
  </tr>
</table>

{{-- Contenedor con fondo beige "cute" --}}
<div style="background: {{ $beige }}; padding: 20px; border-radius: 12px; margin-top: 16px;">
  <h1 style="text-align:center; margin: 0 0 12px; font-weight: 700;">
    ¡Hola, {{ $user->name }}!
  </h1>

  <p style="text-align:center; margin: 0 0 20px; color:#374151;">
    Gracias por registrarte en <strong>Sweet Nanny</strong>.<br>
    Para activar tu cuenta, verifica tu correo con el siguiente botón:
  </p>

  {{-- Botón con estilos inline y buen contraste --}}
  <table role="presentation" align="center" cellpadding="0" cellspacing="0" style="margin: 0 auto 12px;">
    <tr>
      <td style="background: {{ $primary }}; border-radius: 12px; text-align:center;">
        <a href="{{ $url }}"
           style="display:inline-block; padding: 12px 20px; font-weight: 600; text-decoration:none; color: {{ $textOnPrimary }};">
          Verificar mi correo
        </a>
      </td>
    </tr>
  </table>

  <p style="margin: 12px 0 0; color:#6B7280; font-size: 13px; text-align:center;">
    Si no creaste esta cuenta, puedes ignorar este mensaje.
    El enlace expira en <strong>60 minutos</strong>.
  </p>

  <hr style="border:none; border-top:1px solid #E5E7EB; margin: 20px 0;">

  {{-- Fallback de enlace plano --}}
  <p style="color:#6B7280; font-size: 12px;">
    Si el botón no funciona, copia y pega esta URL en tu navegador:<br>
    <a href="{{ $url }}" style="color: {{ $textOnPrimary }}; word-break: break-all;">{{ $url }}</a>
  </p>

  <p style="margin-top: 16px;">
    Saludos,<br>
    {{ config('app.name') }}
  </p>
</div>

{{-- Barra inferior fina --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin:16px 0 0;padding:0;">
  <tr>
    <td style="background: {{ $primary }}; height: 40px; line-height: 6px; font-size:0;">&nbsp;</td>
  </tr>
</table>
@endcomponent
