<?php

namespace App\Services;

use App\Models\NotificationModel;
use App\Models\UserModel;

class NotificationService
{
    public function notify(int $userId, string $type, string $message, ?string $url = null): void
    {
        (new NotificationModel())->insert([
            'user_id' => $userId,
            'type'    => $type,
            'message' => $message,
            'url'     => $url,
        ]);
    }

    public function notifyAdmins(string $type, string $message, ?string $url = null): void
    {
        $admins = (new UserModel())->where('role', 'admin')->where('is_active', 1)->findAll();
        foreach ($admins as $a) {
            $this->notify((int) $a['id'], $type, $message, $url);
        }
    }
}
