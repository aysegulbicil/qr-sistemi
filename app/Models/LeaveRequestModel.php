<?php

namespace App\Models;

use CodeIgniter\Model;

class LeaveRequestModel extends Model
{
    protected $table         = 'leave_requests';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['user_id', 'leave_type_id', 'start_date', 'end_date', 'days', 'reason', 'status', 'approver_id', 'decided_at', 'note'];

    public function forUser(int $userId): array
    {
        return $this->db->table('leave_requests lr')
            ->select('lr.*, t.name AS type_name')
            ->join('leave_types t', 't.id = lr.leave_type_id', 'left')
            ->where('lr.user_id', $userId)
            ->orderBy('lr.created_at', 'DESC')
            ->get()->getResultArray();
    }

    public function pending(): array
    {
        return $this->db->table('leave_requests lr')
            ->select('lr.*, t.name AS type_name, u.full_name')
            ->join('leave_types t', 't.id = lr.leave_type_id', 'left')
            ->join('users u', 'u.id = lr.user_id', 'left')
            ->where('lr.status', 'pending')
            ->orderBy('lr.created_at', 'ASC')
            ->get()->getResultArray();
    }

    /** Tüm izin talepleri (bekleyen + onaylı + reddedilmiş); bekleyenler üstte, sonra en yeni karar/talep. */
    public function allWithUser(): array
    {
        return $this->db->table('leave_requests lr')
            ->select('lr.*, t.name AS type_name, u.full_name')
            ->join('leave_types t', 't.id = lr.leave_type_id', 'left')
            ->join('users u', 'u.id = lr.user_id', 'left')
            ->orderBy("CASE WHEN lr.status = 'pending' THEN 0 ELSE 1 END ASC", '', false)
            ->orderBy('lr.decided_at', 'DESC')
            ->orderBy('lr.created_at', 'DESC')
            ->get()->getResultArray();
    }

    /** Dates (Y-m-d) within an approved leave for a user, for payroll exclusion. */
    public function approvedDates(int $userId, string $start, string $end): array
    {
        $rows = $this->where('user_id', $userId)
            ->where('status', 'approved')
            ->where('start_date <=', $end)
            ->where('end_date >=', $start)
            ->findAll();

        $dates = [];
        foreach ($rows as $r) {
            for ($d = strtotime($r['start_date']); $d <= strtotime($r['end_date']); $d = strtotime('+1 day', $d)) {
                $dates[date('Y-m-d', $d)] = true;
            }
        }

        return $dates;
    }
}
