<?php

namespace App\Models;

use CodeIgniter\Model;

class AdvanceModel extends Model
{
    protected $table         = 'advances';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['user_id', 'type', 'amount', 'reason', 'period_year', 'period_month', 'created_by'];

    public function forUserPeriod(int $userId, int $year, int $month): array
    {
        return $this->where('user_id', $userId)
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /** @return array{advance:float,deduction:float} */
    public function totals(int $userId, int $year, int $month): array
    {
        $rows = $this->select('type, SUM(amount) AS total')
            ->where('user_id', $userId)
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->groupBy('type')
            ->findAll();

        $out = ['advance' => 0.0, 'deduction' => 0.0];
        foreach ($rows as $r) {
            $out[$r['type']] = (float) $r['total'];
        }

        return $out;
    }
}
