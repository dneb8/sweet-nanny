# Email Logo

This directory contains the logo used in email templates.

## logo-email.png

- **Usage**: Embedded inline in authentication emails using CID (Content-ID)
- **Format**: PNG with transparent background
- **Recommended size**: 512x512px
- **Current**: Simple placeholder with "SN" text on purple circle

### Replacing the Logo

To replace the placeholder with your actual logo:

1. Create a PNG image (512x512px recommended, transparent background)
2. Name it `logo-email.png`
3. Place it in `public/images/logo-email.png`
4. The logo will be automatically embedded in all authentication emails

### How It Works

The logo is embedded using Symfony's email embedding feature in `AppServiceProvider`:

```php
$mail->withSymfonyMessage(function (\Symfony\Component\Mime\Email $email) {
    $email->embedFromPath(public_path('images/logo-email.png'), 'logo_cid');
});
```

And referenced in email templates using CID:

```html
<img src="cid:logo_cid" alt="Sweet Nanny" class="email-logo">
```

This ensures the logo displays correctly even if the email client blocks external images.
