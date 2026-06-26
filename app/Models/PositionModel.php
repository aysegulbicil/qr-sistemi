<?php

namespace App\Models;

use CodeIgniter\Model;

class PositionModel extends Model
{
    protected $table         = 'positions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['name', 'department_id', 'description'];

    public function withDepartment(): array
    {
        return $this->db->table('positions p')
            ->select('p.*, d.name AS department_name')
            ->join('departments d', 'd.id = p.department_id', 'left')
            ->orderBy('p.name', 'ASC')
            ->get()->getResultArray();
    }
}
