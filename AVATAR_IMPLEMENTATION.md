# Avatar Implementation Guide

## Overview

This implementation adds profile picture (avatar) upload, update, and delete functionality for users with roles `nanny` and `tutor`. Images are stored in Amazon S3 using Spatie Media Library.

## Backend Implementation

### 1. Spatie Media Library Integration

The `User` model now implements the `HasMedia` interface and uses the `InteractsWithMedia` trait:

```php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    use InteractsWithMedia;
    
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk('s3')           // Uses S3 disk
            ->singleFile();           // Single profile photo (replaces on upload)
    }
}
```

### 2. Controller Methods

`ProfileController` now includes three new methods:

#### `edit()` - Enhanced to return avatar URL
```php
public function edit(Request $request): Response
{
    $user = $request->user();
    $avatarUrl = $user->getFirstMediaUrl('images');
    
    return Inertia::render('settings/Profile', [
        'mustVerifyEmail' => $user instanceof MustVerifyEmail,
        'status' => $request->session()->get('status'),
        'avatarUrl' => $avatarUrl ?: null,
    ]);
}
```

#### `updateAvatar()` - Upload/replace avatar
```php
public function updateAvatar(Request $request): RedirectResponse
{
    $request->validate([
        'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'], // max 4MB
    ]);
    
    $user = $request->user();
    $user->addMediaFromRequest('avatar')
        ->toMediaCollection('images', 's3');
    
    return to_route('profile.edit')->with('success', 'Foto de perfil actualizada correctamente.');
}
```

#### `deleteAvatar()` - Remove avatar
```php
public function deleteAvatar(Request $request): RedirectResponse
{
    $user = $request->user();
    $user->clearMediaCollection('images');
    
    return to_route('profile.edit')->with('success', 'Foto de perfil eliminada correctamente.');
}
```

### 3. Routes

Two new routes were added to `routes/settings.php`:

```php
Route::post('settings/profile/avatar', [ProfileController::class, 'updateAvatar'])
    ->name('profile.avatar.update');
Route::delete('settings/profile/avatar', [ProfileController::class, 'deleteAvatar'])
    ->name('profile.avatar.delete');
```

### 4. Validation Rules

- **Format**: jpeg, png, jpg, webp
- **Max Size**: 4 MB (4096 KB)
- **Required**: File must be present when uploading

## Frontend Implementation

### 1. TypeScript Interface Update

The `User` interface now includes an optional `avatar_url` field:

```typescript
export interface User {
  ulid: string;
  name: string;
  surnames: string;
  email: string;
  email_verified_at: date;
  number: string;
  roles: Array<Rol>;
  tutor?: Tutor; 
  nanny?: Nanny;
  avatar_url?: string;  // NEW
  created_at: string;
}
```

### 2. Profile.vue Component

The Profile page (`resources/js/Pages/settings/Profile.vue`) now includes:

- **Avatar Display**: Circular avatar with fallback icon when no image exists
- **File Selection**: Hidden file input with styled button trigger
- **Preview**: Real-time preview of selected image before upload
- **Upload Actions**: Save or cancel preview
- **Delete Button**: Remove existing avatar
- **Loading States**: Visual feedback during upload/delete operations
- **Error Messages**: Display validation errors

#### Key Features:
- Image preview before upload
- Loading spinner during operations
- Success/error message display
- Accepts drag-and-drop (via native file input)
- Responsive design with Tailwind CSS

## Testing

### Test Coverage

All avatar functionality is covered by tests in `tests/Feature/Settings/ProfileAvatarTest.php`:

1. ✅ User can upload avatar
2. ✅ Avatar must be an image
3. ✅ Avatar must not exceed 4MB
4. ✅ Avatar must be jpeg, png, jpg or webp
5. ✅ Avatar upload replaces existing avatar (singleFile behavior)
6. ✅ User can delete avatar
7. ✅ Avatar URL is returned when user has avatar
8. ✅ Avatar URL is empty when no avatar is uploaded

All tests pass successfully.

### Running Tests

```bash
php artisan test tests/Feature/Settings/ProfileAvatarTest.php
```

## Environment Configuration

Ensure these variables are set in your `.env` file:

```env
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_bucket_name
AWS_USE_PATH_STYLE_ENDPOINT=false
```

## Usage Example

### Backend (Controller or Service)

```php
// Upload avatar
$user->addMedia($file)->toMediaCollection('images', 's3');

// Get avatar URL
$avatarUrl = $user->getFirstMediaUrl('images');

// Delete avatar
$user->clearMediaCollection('images');

// Check if user has avatar
$hasAvatar = $user->hasMedia('images');
```

### Frontend (Vue Component)

```vue
<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

const avatarForm = useForm({
    avatar: null as File | null,
});

const submitAvatar = () => {
    avatarForm.post(route('profile.avatar.update'), {
        preserveScroll: true,
    });
};
</script>
```

## Security Considerations

1. **File Type Validation**: Only images (jpeg, png, jpg, webp) are accepted
2. **File Size Limit**: Maximum 4MB to prevent abuse
3. **Authentication**: All avatar routes require authentication
4. **S3 Permissions**: Ensure proper S3 bucket policies are configured
5. **CSRF Protection**: All POST/DELETE requests include CSRF token

## Future Enhancements (Optional)

- [ ] Add facial detection validation
- [ ] Image optimization/compression before upload
- [ ] Multiple image sizes (thumbnails)
- [ ] Crop functionality
- [ ] Avatar from URL/social media
- [ ] Default avatar generator (initials, patterns)

## Notes

- The `singleFile()` configuration ensures only one avatar per user
- New uploads automatically replace the previous avatar
- Images are stored in S3 with unique names
- Empty avatar URL returns empty string, not null
- The frontend displays a user icon fallback when no avatar exists
