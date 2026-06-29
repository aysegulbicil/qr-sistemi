<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Aylık puantaj/maaş snapshot'ı. Bir ay "dondurulduğunda" her personel için
 * o anki hesap PayrollService'ten alınıp buraya yazılır; kapalı ay artık
 * geçmiş log düzeltmelerinden etkilenmez.
 */
class PayrollRunModel extends Model
{
    protected $table         = 'payroll_runs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'user_id', 'period_year', 'period_month',
        'present_days', 'expected_days', 'missing_days', 'leave_days',
        'late_minutes', 'overtime_minutes', 'worked_minutes',
        'salary_type', 'salary_amount', 'base_salary', 'overtime_pay', 'overtime_mult',
        'advances_total', 'deductions_total', 'net_pay', 'currency',
        'generated_by', 'generated_at',
    ];

    public function isClosed(int $year, int $month): bool
    {
        return $this->where('period_year', $year)->where('period_month', $month)->countAllResults() > 0;
    }

    /** Period snapshot rows keyed by user_id. */
    public function forPeriod(int $year, int $month): array
    {
        $out = [];
        foreach ($this->where('period_year', $year)->where('period_month', $month)->findAll() as $r) {
            $out[(int) $r['user_id']] = $r;
        }

        return $out;
    }

    public function forUserPeriod(int $userId, int $year, int $month): ?array
    {
        return $this->where('user_id', $userId)->where('period_year', $year)->where('period_month', $month)->first();
    }

    public function closePeriod(int $year, int $month, array $snapshots): void
    {
        $this->where('period_year', $year)->where('period_month', $month)->delete();
        if ($snapshots !== []) {
            $this->insertBatch($snapshots);
        }
    }

    public function reopenPeriod(int $year, int $month): void
    {
        $this->where('period_year', $year)->where('period_month', $month)->delete();
    }
}
