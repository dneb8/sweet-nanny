<?php

namespace App\Http\Traits;

use App\Jobs\ValidateAvatarMedia;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

trait HandlesAvatarValidation
{
    /**
     * If the user has media 'images' with status 'pending',
     * trigger the validation Job (once per time window).
     */
    protected function kickoffAvatarValidationIfNeeded(User $user): void
    {
        $media = $user->getFirstMedia('images');
        if (! $media) {
            return;
        }

        $status = $media->getCustomProperty('status', 'approved');
        if ($status !== 'pending') {
            return;
        }

        // Avoid re-queuing spam (30s lock; adjust as needed)
        $lockKey = "validate-avatar:{$user->id}:{$media->id}";
        $lockDurationSeconds = 30;
        $gotLock = Cache::lock($lockKey, $lockDurationSeconds)->get();

        if (! $gotLock) {
            return;
        }

        // Queue validation in background
        ValidateAvatarMedia::dispatch($user->id, $media->id)->onQueue('default');
    }
}
