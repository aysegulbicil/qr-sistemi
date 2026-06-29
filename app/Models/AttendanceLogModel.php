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

    /** Son kayıtlar — personel + lokasyon adıyla (admin düzeltme ekranı). */
    public function recentDetailed(int $limit = 300): array
    {
        return $this->db->table('attendance_logs a')
            ->select('a.*, u.full_name, l.name AS location_name')
            ->join('users u', 'u.id = a.user_id', 'left')
            ->join('locations l', 'l.id = a.location_id', 'left')
            ->orderBy('a.event_at', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    /** Açık kayıtlar: son hareketi "giriş" olan (çıkış yapılmamış) personel. */
    public function openCheckIns(): array
    {
        $sql = 'SELECT a.*, u.full_name'
             . ' FROM attendance_logs a'
             . ' JOIN (SELECT user_id, MAX(event_at) AS max_at FROM attendance_logs GROUP BY user_id) m'
             . '   ON m.user_id = a.user_id AND m.max_at = a.event_at'
             . ' LEFT JOIN users u ON u.id = a.user_id'
             . " WHERE a.type = 'in'"
             . ' ORDER BY a.event_at ASC';

        return $this->db->query($sql)->getResultArray();
    }

    /** Tek kayıt — personel + lokasyon adıyla. */
    public function findDetailed(int $id): ?array
    {
        return $this->db->table('attendance_logs a')
            ->select('a.*, u.full_name, l.name AS location_name')
            ->join('users u', 'u.id = a.user_id', 'left')
            ->join('locations l', 'l.id = a.location_id', 'left')
            ->where('a.id', $id)
            ->get()->getRowArray() ?: null;
    }
}
