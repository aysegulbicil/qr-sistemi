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

    /** Listing with search (name/code/username), department & status filters, and safe sorting. */
    public function listDetailed(string $search = '', string $sort = 'full_name', string $dir = 'asc', ?int $departmentId = null, string $status = ''): array
    {
        $b = $this->detailed();

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

        $allowed = ['full_name', 'employee_code', 'department_name', 'position_name', 'employment_status', 'role'];
        $sort    = in_array($sort, $allowed, true) ? $sort : 'full_name';
        $dir     = strtolower($dir) === 'desc' ? 'DESC' : 'ASC';

        return $b->orderBy($sort, $dir)->get()->getResultArray();
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
