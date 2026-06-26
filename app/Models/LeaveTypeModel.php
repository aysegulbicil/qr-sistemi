<?php

namespace App\Models;

use CodeIgniter\Model;

class LeaveTypeModel extends Model
{
    protected $table         = 'leave_types';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['name', 'is_paid'];

    public function ordered(): array
    {
        return $this->orderBy('name', 'ASC')->findAll();
    }
}
