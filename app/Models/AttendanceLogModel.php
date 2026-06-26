<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceLogModel extends Model
{
    protected $table         = 'attendance_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $updatedField  = '';
    protected $allowedFields = [
        'user_id', 'location_id', 'type', 'event_at',
        'source', 'ip_address', 'user_agent', 'qr_token_id', 'note',
        'geo_lat', 'geo_lng', 'distance_m', 'is_suspicious', 'suspicious_reason',
    ];

    public function lastForUser(int $userId): ?array
    {
        return $this->where('user_id', $userId)
            ->orderBy('event_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function isCheckedIn(int $userId): bool
    {
        $last = $this->lastForUser($userId);

        return $last !== null && $last['type'] === 'in';
    }

    public function forUserBetween(int $userId, string $startDate, string $endDate): array
    {
        return $this->where('user_id', $userId)
            ->where('event_at >=', $startDate . ' 00:00:00')
            ->where('event_at <=', $endDate . ' 23:59:59')
            ->orderBy('event_at', 'ASC')
            ->findAll();
    }
}
