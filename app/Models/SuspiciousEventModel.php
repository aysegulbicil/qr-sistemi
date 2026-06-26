<?php

namespace App\Models;

use CodeIgniter\Model;

class SuspiciousEventModel extends Model
{
    protected $table         = 'suspicious_events';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $updatedField  = '';
    protected $allowedFields = ['user_id', 'location_id', 'type', 'reason', 'geo_lat', 'geo_lng', 'distance_m', 'ip_address'];

    public function recent(int $limit = 100): array
    {
        return $this->db->table('suspicious_events s')
            ->select('s.*, u.full_name, l.name AS location_name')
            ->join('users u', 'u.id = s.user_id', 'left')
            ->join('locations l', 'l.id = s.location_id', 'left')
            ->orderBy('s.created_at', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }
}
