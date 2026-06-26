<?php

namespace App\Models;

use CodeIgniter\Model;

class AdvanceRequestModel extends Model
{
    protected $table         = 'advance_requests';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['user_id', 'amount', 'reason', 'status', 'approver_id', 'decided_at', 'period_year', 'period_month'];

    public function forUser(int $userId): array
    {
        return $this->where('user_id', $userId)->orderBy('created_at', 'DESC')->findAll();
    }

    public function pending(): array
    {
        return $this->db->table('advance_requests ar')
            ->select('ar.*, u.full_name')
            ->join('users u', 'u.id = ar.user_id', 'left')
            ->where('ar.status', 'pending')
            ->orderBy('ar.created_at', 'ASC')
            ->get()->getResultArray();
    }
}
