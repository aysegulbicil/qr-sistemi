<?php

namespace App\Models;

use CodeIgniter\Model;

class ShiftAssignmentModel extends Model
{
    protected $table         = 'shift_assignments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['user_id', 'work_date', 'shift_id', 'note'];

    /** Assignments for a user within a date range, keyed by date. */
    public function forUserBetween(int $userId, string $start, string $end): array
    {
        $rows = $this->where('user_id', $userId)
            ->where('work_date >=', $start)
            ->where('work_date <=', $end)
            ->findAll();

        $byDate = [];
        foreach ($rows as $r) {
            $byDate[$r['work_date']] = $r;
        }

        return $byDate;
    }

    /** Create or update the assignment for a given user + date. */
    public function assign(int $userId, string $date, int $shiftId, ?string $note = null): void
    {
        $existing = $this->where('user_id', $userId)->where('work_date', $date)->first();
        if ($existing !== null) {
            $this->update($existing['id'], ['shift_id' => $shiftId, 'note' => $note]);
        } else {
            $this->insert(['user_id' => $userId, 'work_date' => $date, 'shift_id' => $shiftId, 'note' => $note]);
        }
    }
}
