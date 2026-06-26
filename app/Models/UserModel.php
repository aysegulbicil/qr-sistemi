<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'employee_code', 'full_name', 'email', 'username', 'password_hash', 'role', 'shift_id', 'is_active',
        'department_id', 'position_id', 'salary_type', 'salary_amount', 'employment_status',
        'phone', 'contact_email', 'address', 'national_id', 'iban', 'hire_date', 'birth_date', 'photo_path',
    ];

    public function findByUsername(string $username): ?array
    {
        return $this->where('username', $username)->first();
    }

    /** Simple ordered list (kept for backward compatibility). */
    public function employees(): array
    {
        return $this->orderBy('full_name', 'ASC')->findAll();
    }

    /** Base query joined with department & position. */
    private function detailed()
    {
        return $this->db->table('users u')
            ->select('u.*, d.name AS department_name, p.name AS position_name')
            ->join('departments d', 'd.id = u.department_id', 'left')
            ->join('positions p', 'p.id = u.position_id', 'left');
    }

    /** Apply search (name/code/username), department & status filters to a builder. */
    private function applyListFilters($b, string $search, ?int $departmentId, string $status)
    {
        if ($search !== '') {
            $b->groupStart()
                ->like('u.full_name', $search)
                ->orLike('u.employee_code', $search)
                ->orLike('u.username', $search)
                ->groupEnd();
        }
        if ($departmentId) {
            $b->where('u.department_id', $departmentId);
        }
        if ($status !== '') {
            $b->where('u.employment_status', $status);
        }

        return $b;
    }

    /** Listing with search, filters, safe sorting and optional pagination. */
    public function listDetailed(string $search = '', string $sort = 'full_name', string $dir = 'asc', ?int $departmentId = null, string $status = '', ?int $limit = null, int $offset = 0): array
    {
        $b = $this->applyListFilters($this->detailed(), $search, $departmentId, $status);

        $allowed = ['full_name', 'employee_code', 'department_name', 'position_name', 'employment_status', 'role'];
        $sort    = in_array($sort, $allowed, true) ? $sort : 'full_name';
        $dir     = strtolower($dir) === 'desc' ? 'DESC' : 'ASC';
        $b->orderBy($sort, $dir);

        if ($limit !== null) {
            $b->limit($limit, max(0, $offset));
        }

        return $b->get()->getResultArray();
    }

    /** Row count for the same filters (pagination). */
    public function countDetailed(string $search = '', ?int $departmentId = null, string $status = ''): int
    {
        return $this->applyListFilters($this->detailed(), $search, $departmentId, $status)->countAllResults();
    }

    /** Detailed rows for a set of ids (CSV export). */
    public function forExport(array $ids): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if ($ids === []) {
            return [];
        }

        return $this->detailed()->whereIn('u.id', $ids)->orderBy('u.full_name', 'ASC')->get()->getResultArray();
    }

    /** Bulk employment-status update. Returns affected id count. */
    public function bulkStatus(array $ids, string $status): int
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if ($ids === [] || ! in_array($status, ['active', 'passive', 'terminated'], true)) {
            return 0;
        }
        $this->db->table('users')->whereIn('id', $ids)->update([
            'employment_status' => $status,
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);

        return count($ids);
    }

    public function findDetailed(int $id): ?array
    {
        return $this->detailed()->where('u.id', $id)->get()->getRowArray() ?: null;
    }

    public function countByStatus(string $status): int
    {
        return $this->where('employment_status', $status)->countAllResults();
    }
}
