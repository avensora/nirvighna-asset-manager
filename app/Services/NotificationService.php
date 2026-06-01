<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public static function notify(User|int $user, string $type, string $title, string $body, array $data = []): void
    {
        $userId = $user instanceof User ? $user->id : $user;

        Notification::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'body'    => $body,
            'data'    => $data ?: null,
        ]);
    }

    /**
     * Notify only if no existing unread notification of the same type+data combination exists.
     * Prevents duplicate overdue-invoice notifications.
     */
    public static function notifyOnce(User|int $user, string $type, string $title, string $body, array $data = []): void
    {
        $userId = $user instanceof User ? $user->id : $user;

        $exists = Notification::where('user_id', $userId)
            ->where('type', $type)
            ->whereNull('read_at')
            ->when(! empty($data), function ($q) use ($data) {
                $q->where('data', json_encode($data));
            })
            ->exists();

        if (! $exists) {
            static::notify($userId, $type, $title, $body, $data);
        }
    }
}
