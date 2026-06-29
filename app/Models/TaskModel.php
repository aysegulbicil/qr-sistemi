<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Admin -> personel to-do görevleri. Yoklama/maaşa dokunmayan izole tablo.
 */
class TaskModel extends Model
{
    protected $table         = 'tasks';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'user_id', 'title', 'description', 'priority', 'status', 'due_date', 'assigned_by', 'completed_at',
    ];

    public const STATUSES   = ['pending', 'in_progress', 'done', 'cancelled'];
    public const PRIORITIES = ['low', 'normal', 'high'];

    /** Admin liste — atanan + atayan adıyla, opsiyonel filtreyle. */
    public function listDetailed(?int $userId = null, string $status = ''): array
    {
        $b = $this->db->table('tasks t')
            ->select('t.*, u.full_name AS assignee_name, a.full_name AS assigner_name')
            ->join('users u', 'u.id = t.user_id', 'left')
            ->join('users a', 'a.id = t.assigned_by', 'left');
        if ($userId) {
            $b->where('t.user_id', $userId);
        }
        if ($status !== '') {
            $b->where('t.status', $status);
        }

        return $b->orderBy('t.id', 'DESC')->get()->getResultArray();
    }

    /** Personelin kendi görevleri — açık olanlar (yapılıyor/bekliyor) önce. */
    public function forUser(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->orderBy("FIELD(status, 'in_progress', 'pending', 'done', 'cancelled')", '', false)
            ->orderBy('due_date', 'ASC')
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    public function countOpenForUser(int $userId): int
    {
        return $this->where('user_id', $userId)->whereIn('status', ['pending', 'in_progress'])->countAllResults();
    }

    /** Durum güncelle. $ownerId verilirse sahiplik doğrulanır (personel kendi görevi). */
    public function markStatus(int $id, string $status, ?int $ownerId = null): bool
    {
        if (! in_array($status, self::STATUSES, true)) {
            return false;
        }
        $row = $this->find($id);
        if ($row === null) {
            return false;
        }
        if ($ownerId !== null && (int) $row['user_id'] !== $ownerId) {
            return false;
        }
        $this->update($id, [
            'status'       => $status,
            'completed_at' => $status === 'done' ? ($row['completed_at'] ?: date('Y-m-d H:i:s')) : null,
        ]);

        return true;
    }
}
