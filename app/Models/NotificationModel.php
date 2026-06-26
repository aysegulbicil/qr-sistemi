<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table         = 'notifications';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $updatedField  = '';
    protected $allowedFields = ['user_id', 'type', 'message', 'url', 'read_at'];

    public function forUser(int $userId, int $limit = 50): array
    {
        return $this->where('user_id', $userId)->orderBy('created_at', 'DESC')->limit($limit)->findAll();
    }

    public function unreadCount(int $userId): int
    {
        return $this->where('user_id', $userId)->where('read_at', null)->countAllResults();
    }

    public function markAllRead(int $userId): void
    {
        $this->where('user_id', $userId)->where('read_at', null)->set('read_at', date('Y-m-d H:i:s'))->update();
    }
}
