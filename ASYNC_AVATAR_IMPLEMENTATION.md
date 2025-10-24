# Async Avatar Upload with AWS Rekognition Validation

## Overview

This implementation provides **asynchronous avatar validation** using AWS Rekognition with real-time notifications. Users upload images that are validated in the background for appropriate content and facial detection, then receive notifications about the validation result.

## Architecture

### Flow Diagram

```
User Uploads Avatar
       ↓
Store in temp S3 location (tmp/avatars/{ulid}/{uuid}.ext)
       ↓
Dispatch ProcessAvatarUpload Job
       ↓
Return "Under Review" Message (immediate response)
       ↓
[Background Job Queue]
       ↓
AWS Rekognition Validation
   ├── DetectModerationLabels (block explicit/violence content)
   └── DetectFaces (require exactly 1 face)
       ↓
   Success?
   ├── Yes → Save to Spatie Media Library → Success Notification
   └── No → Delete temp file → Failure Notification
       ↓
Notification stored in database + broadcast
       ↓
Frontend: Bell icon updates + Toast shown
       ↓
User clicks notification → Redirect to /settings/profile
```

## Backend Implementation

### 1. Job: ProcessAvatarUpload

**Location**: `app/Jobs/ProcessAvatarUpload.php`

**Responsibilities**:
- Validate image with AWS Rekognition
- Check for inappropriate content (moderation labels)
- Verify exactly 1 face is present
- Save to Spatie Media Library on success
- Send notification with result

**Banned Labels** (min 80% confidence):
- Explicit Nudity
- Sexual Activity
- Sexual Content
- Graphic Male Nudity
- Graphic Female Nudity
- Violence
- Hate Symbols

**Parameters**:
```php
ProcessAvatarUpload::dispatch(
    user: $user,              // User model
    tmpKey: $tmpKey,          // S3 temp key (tmp/avatars/...)
    minConfidence: 80         // Confidence threshold (default 80%)
);
```

### 2. Notification: AvatarProcessed

**Location**: `app/Notifications/AvatarProcessed.php`

**Channels**: `database`, `broadcast`

**Data Structure**:
```php
[
    'success' => true|false,
    'message' => 'Mensaje descriptivo',
    'redirect' => '/settings/profile',
    'type' => 'avatar_processed',
]
```

### 3. Controller Updates

**ProfileController::updateAvatar()** - Modified for async processing:

```php
public function updateAvatar(Request $request): RedirectResponse
{
    // Validate file
    $request->validate([
        'avatar' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
    ]);

    // Store in temp S3 location
    $tmpKey = 'tmp/avatars/' . $user->ulid . '/' . Str::uuid() . '.ext';
    Storage::disk('s3')->put($tmpKey, file_get_contents($file->getRealPath()));

    // Dispatch background job
    ProcessAvatarUpload::dispatch($user, $tmpKey);

    // Immediate response
    return to_route('profile.edit')->with('info', 'Tu imagen está siendo validada...');
}
```

**NotificationController** - New endpoints:

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/notifications` | List all notifications with unread count |
| POST | `/notifications/{id}/read` | Mark specific notification as read |
| POST | `/notifications/read-all` | Mark all notifications as read |
| GET | `/notifications/unread-count` | Get unread notification count |

### 4. Database Migration

**Table**: `notifications`

| Column | Type | Description |
|--------|------|-------------|
| id | UUID | Primary key |
| type | string | Notification class name |
| notifiable_type | string | User model type |
| notifiable_id | bigint | User ID |
| data | text | Notification payload (JSON) |
| read_at | timestamp | When read (null = unread) |
| created_at | timestamp | Creation time |
| updated_at | timestamp | Update time |

## Frontend Implementation

### 1. Bell Icon Component

**Location**: `resources/js/components/NotificationDropdown.vue`

**Features**:
- Bell icon with red badge showing unread count
- Dropdown menu (320px wide, max 300px height)
- Scrollable notification list
- Visual indicators:
  - ✓ Green background for success
  - ✗ Red background for failure
  - Blue dot for unread notifications
- Relative time display in Spanish
- Click to mark as read and navigate

**Usage** (already integrated):
```vue
<NotificationDropdown />
```

### 2. Notification State Management

**Location**: `resources/js/composables/useNotifications.ts`

**Exported Functions**:
```typescript
const {
    notifications,              // ref<Notification[]>
    unreadCount,               // ref<number>
    unreadNotifications,       // computed<Notification[]>
    loading,                   // ref<boolean>
    fetchNotifications,        // () => Promise<void>
    markAsRead,                // (id: string) => Promise<void>
    markAllAsRead,             // () => Promise<void>
    handleNotificationClick,   // (notification: Notification) => void
    handleNewNotification,     // (data: any) => void
} = useNotifications();
```

**Auto-Polling**: Fetches new notifications every 30 seconds

### 3. Integration Points

**AppSidebarHeader.vue**:
```vue
<NotificationDropdown />  <!-- Bell icon with badge -->
```

**Profile.vue** - Shows toast on upload:
```typescript
avatarForm.post(route('profile.avatar.update'), {
    onSuccess: () => {
        toast.info('Tu imagen está siendo validada. Recibirás una notificación cuando esté lista.');
    },
});
```

### 4. UI Features

**Bell Icon**:
- Default state: Gray bell icon
- With notifications: Red badge with count ("1", "2", ... "9+")
- Hover: Background highlight

**Notification Item**:
- Icon: ✓ (success) or ✗ (failure)
- Color-coded background
- Message text
- Relative time ("Hace 5 min", "Hace 2 h", "Hace 3 d")
- Unread indicator (blue dot on right)

**Empty State**: "Sin notificaciones"

## AWS Configuration

### Required Environment Variables

```env
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-2
AWS_BUCKET=sweet-nanny
```

### Required IAM Permissions

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "rekognition:DetectFaces",
                "rekognition:DetectModerationLabels"
            ],
            "Resource": "*"
        },
        {
            "Effect": "Allow",
            "Action": [
                "s3:GetObject",
                "s3:PutObject",
                "s3:DeleteObject"
            ],
            "Resource": "arn:aws:s3:::sweet-nanny/*"
        }
    ]
}
```

### S3 Bucket Structure

```
sweet-nanny/
├── tmp/
│   └── avatars/
│       └── {user-ulid}/
│           └── {uuid}.{ext}  ← Temporary upload location
└── {media-library-id}/
    └── {filename}  ← Final location (Spatie Media Library)
```

## Testing

### Queue Worker

Start the queue worker to process background jobs:

```bash
php artisan queue:work
```

Or for development with auto-reload:

```bash
php artisan queue:listen
```

### Manual Testing Steps

1. **Upload an avatar** at `/settings/profile`
   - ✅ Toast message appears: "Tu imagen está siendo validada..."
   - ✅ File uploaded to `tmp/avatars/{ulid}/...` in S3

2. **Check queue** is processing the job
   ```bash
   php artisan queue:work --once
   ```

3. **Verify Rekognition** validation:
   - Test with valid image (1 face, appropriate) → Success notification
   - Test with no face → Rejection notification
   - Test with multiple faces → Rejection notification
   - Test with inappropriate content → Rejection notification

4. **Check notification system**:
   - ✅ Bell icon shows badge with unread count
   - ✅ Click bell → dropdown opens
   - ✅ Notification appears in list
   - ✅ Click notification → marks as read + redirects to profile
   - ✅ Unread count decrements

5. **Verify final result**:
   - Success: Avatar appears in profile (Spatie Media Library)
   - Failure: No avatar saved, temp file deleted

### Test Images

**Valid Image**: Single face, no inappropriate content
```bash
# Create test user and upload valid selfie
```

**Invalid Images**:
- No face detected
- Multiple faces
- Inappropriate content (will be blocked)

## Notification Messages

### Success
```
¡Tu foto de perfil ha sido actualizada exitosamente!
```

### Rejection - No Face
```
Tu imagen fue rechazada porque no se detectó ningún rostro.
```

### Rejection - Multiple Faces
```
Tu imagen fue rechazada porque se detectaron múltiples rostros. Solo debe haber uno.
```

### Rejection - Inappropriate Content
```
Tu imagen fue rechazada por contener contenido inapropiado.
```

### Error
```
Ocurrió un error al procesar tu imagen. Por favor, intenta nuevamente.
```

## Broadcasting (Optional Enhancement)

To enable real-time notifications without polling:

1. **Install Laravel Echo** (if not already):
   ```bash
   npm install --save laravel-echo pusher-js
   ```

2. **Configure broadcasting** in `.env`:
   ```env
   BROADCAST_CONNECTION=pusher
   PUSHER_APP_ID=your-app-id
   PUSHER_APP_KEY=your-app-key
   PUSHER_APP_SECRET=your-app-secret
   ```

3. **Update useNotifications.ts** to listen for broadcasts:
   ```typescript
   // Listen for real-time notifications
   window.Echo.private(`App.Models.User.${userId}`)
       .notification((notification: any) => {
           handleNewNotification(notification);
       });
   ```

## Troubleshooting

### Issue: Queue not processing

**Solution**: Ensure queue worker is running
```bash
php artisan queue:work
```

### Issue: AWS credentials error

**Solution**: Verify `.env` variables and IAM permissions
```bash
aws rekognition detect-faces --image "S3Object={Bucket=sweet-nanny,Name=test.jpg}"
```

### Issue: Notifications not appearing

**Solution**: Check database and run migration
```bash
php artisan migrate
php artisan queue:work --once
```

### Issue: Bell icon not updating

**Solution**: Frontend is polling every 30s. Force refresh:
```javascript
// In browser console
location.reload();
```

## Security Considerations

1. **Private S3 bucket**: Images are not publicly accessible
2. **Temporary files**: Deleted after processing (success or failure)
3. **Confidence threshold**: 80% minimum for moderation labels
4. **Authentication**: All endpoints require authenticated user
5. **CSRF protection**: Enabled on all POST/DELETE requests
6. **Rate limiting**: Consider adding to notification endpoints

## Performance

- **Upload time**: Immediate (temp file stored quickly)
- **Validation time**: 2-5 seconds (Rekognition API calls)
- **Total time to notification**: 5-10 seconds (includes queue delay)
- **Frontend polling**: Every 30 seconds (configurable)

## Future Enhancements

- [ ] WebSocket integration for instant notifications (no polling)
- [ ] Email notifications for avatar processing results
- [ ] Image optimization before validation (resize, compress)
- [ ] Admin dashboard for moderation review
- [ ] User notification preferences (email on/off)
- [ ] Batch processing for multiple images
- [ ] Image analytics (age, gender, emotions from Rekognition)
