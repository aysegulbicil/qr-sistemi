<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdvanceModel;
use App\Models\PayrollRunModel;
use App\Models\UserModel;
use App\Services\PayrollService;

class Payroll extends BaseController
{
    public function index()
    {
        [$year, $month] = $this->period();
        $svc      = new PayrollService();
        $runModel = new PayrollRunModel();
        $closed   = $runModel->isClosed($year, $month);
        $snap     = $closed ? $runModel->forPeriod($year, $month) : [];

        $rows     = [];
        $total    = 0.0;
        $currency = '₺';
        $closedAt = null;
        foreach ((new UserModel())->employees() as $u) {
            $uid = (int) $u['id'];
            if ($closed && isset($snap[$uid])) {
                $p        = $this->snapshotToP($snap[$uid]);
                $closedAt = $snap[$uid]['generated_at'] ?? $closedAt;
            } else {
                $p = $svc->computeMonth($u, $year, $month);
            }
            $rows[]   = ['user' => $u, 'p' => $p];
            $total   += $p['net'];
            $currency = $p['currency'];
        }

        return view('admin/payroll/index', [
            'rows'     => $rows,
            'year'     => $year,
            'month'    => $month,
            'ym'       => sprintf('%04d-%02d', $year, $month),
            'monthStr' => $this->monthStr($year, $month),
            'total'    => $total,
            'currency' => $rows[0]['p']['currency'] ?? $currency,
            'closed'   => $closed,
            'closedAt' => $closedAt,
        ]);
    }

    public function show(int $id)
    {
        [$year, $month] = $this->period();
        $u              = (new UserModel())->findDetailed($id);
        if ($u === null) {
            return redirect()->to('/admin/payroll')->with('error', 'Personel bulunamadı.');
        }

        $runModel = new PayrollRunModel();
        $snapRow  = $runModel->forUserPeriod($id, $year, $month);
        $closed   = $runModel->isClosed($year, $month);
        $p        = ($closed && $snapRow !== null)
            ? $this->snapshotToP($snapRow)
            : (new PayrollService())->computeMonth($u, $year, $month);

        return view('admin/payroll/detail', [
            'u'        => $u,
            'p'        => $p,
            'advances' => (new AdvanceModel())->forUserPeriod($id, $year, $month),
            'year'     => $year,
            'month'    => $month,
            'ym'       => sprintf('%04d-%02d', $year, $month),
            'monthStr' => $this->monthStr($year, $month),
            'closed'   => $closed,
            'closedAt' => $snapRow['generated_at'] ?? null,
        ]);
    }

    public function close()
    {
        [$year, $month] = $this->period();
        $svc = new PayrollService();
        $by  = (int) session()->get('user_id');

        $snaps = [];
        foreach ((new UserModel())->employees() as $u) {
            $p       = $svc->computeMonth($u, $year, $month);
            $snaps[] = $this->snapshotRow((int) $u['id'], $year, $month, $p, $by);
        }
        (new PayrollRunModel())->closePeriod($year, $month, $snaps);

        return redirect()->to($this->indexUrl($year, $month))
            ->with('message', $this->monthStr($year, $month) . ' donduruldu — bu ay artık geçmiş düzeltmelerinden etkilenmez.');
    }

    public function reopen()
    {
        [$year, $month] = $this->period();
        (new PayrollRunModel())->reopenPeriod($year, $month);

        return redirect()->to($this->indexUrl($year, $month))
            ->with('message', $this->monthStr($year, $month) . ' yeniden açıldı — değerler tekrar canlı hesaplanıyor.');
    }

    public function addAdvance(int $id)
    {
        if (! $this->validate(['amount' => 'required|decimal|greater_than[0]'])) {
            return redirect()->back()->with('error', 'Geçerli bir tutar gir.');
        }

        [$year, $month] = $this->period();
        if ((new PayrollRunModel())->isClosed($year, $month)) {
            return redirect()->to($this->detailUrl($id, $year, $month))->with('error', 'Bu ay donduruldu. Avans/kesinti için önce ayı yeniden aç.');
        }
        (new AdvanceModel())->insert([
            'user_id'      => $id,
            'type'         => $this->request->getPost('type') === 'deduction' ? 'deduction' : 'advance',
            'amount'       => (float) $this->request->getPost('amount'),
            'reason'       => $this->request->getPost('reason') ?: null,
            'period_year'  => $year,
            'period_month' => $month,
            'created_by'   => (int) session()->get('user_id'),
        ]);

        return redirect()->to($this->detailUrl($id, $year, $month))->with('message', 'Kayıt eklendi.');
    }

    public function deleteAdvance(int $id, int $advId)
    {
        [$year, $month] = $this->period();
        if ((new PayrollRunModel())->isClosed($year, $month)) {
            return redirect()->to($this->detailUrl($id, $year, $month))->with('error', 'Bu ay donduruldu. Avans/kesinti için önce ayı yeniden aç.');
        }
        (new AdvanceModel())->delete($advId);

        return redirect()->to($this->detailUrl($id, $year, $month))->with('message', 'Kayıt silindi.');
    }

    // ---- snapshot yardımcıları ----

    private function snapshotRow(int $userId, int $year, int $month, array $p, int $by): array
    {
        return [
            'user_id'          => $userId,
            'period_year'      => $year,
            'period_month'     => $month,
            'present_days'     => (int) $p['present_days'],
            'expected_days'    => (int) $p['expected_days'],
            'missing_days'     => (int) $p['missing_days'],
            'leave_days'       => (int) ($p['leave_days'] ?? 0),
            'late_minutes'     => (int) $p['late_minutes'],
            'overtime_minutes' => (int) $p['overtime_minutes'],
            'worked_minutes'   => (int) $p['worked_minutes'],
            'salary_type'      => (string) $p['salary_type'],
            'salary_amount'    => (float) $p['salary_amount'],
            'base_salary'      => (float) $p['base'],
            'overtime_pay'     => (float) $p['overtime_pay'],
            'overtime_mult'    => (float) $p['overtime_mult'],
            'advances_total'   => (float) $p['advances_total'],
            'deductions_total' => (float) $p['deductions_total'],
            'net_pay'          => (float) $p['net'],
            'currency'         => (string) $p['currency'],
            'generated_by'     => $by,
            'generated_at'     => date('Y-m-d H:i:s'),
        ];
    }

    private function snapshotToP(array $r): array
    {
        return [
            'present_days'     => (int) $r['present_days'],
            'expected_days'    => (int) $r['expected_days'],
            'missing_days'     => (int) $r['missing_days'],
            'leave_days'       => (int) $r['leave_days'],
            'late_minutes'     => (int) $r['late_minutes'],
            'overtime_minutes' => (int) $r['overtime_minutes'],
            'worked_minutes'   => (int) $r['worked_minutes'],
            'worked_hours'     => ((int) $r['worked_minutes']) / 60,
            'early_minutes'    => 0,
            'hourly'           => 0,
            'base'             => (float) $r['base_salary'],
            'overtime_pay'     => (float) $r['overtime_pay'],
            'overtime_mult'    => (float) $r['overtime_mult'],
            'advances_total'   => (float) $r['advances_total'],
            'deductions_total' => (float) $r['deductions_total'],
            'net'              => (float) $r['net_pay'],
            'salary_type'      => (string) $r['salary_type'],
            'salary_amount'    => (float) $r['salary_amount'],
            'currency'         => (string) $r['currency'],
        ];
    }

    private function period(): array
    {
        $ym    = (string) ($this->request->getGet('month') ?: $this->request->getPost('month') ?: date('Y-m'));
        $parts = array_pad(explode('-', $ym), 2, date('m'));
        $year  = (int) $parts[0];
        $month = (int) $parts[1];
        if ($month < 1 || $month > 12) {
            $month = (int) date('m');
        }
        if ($year < 2000) {
            $year = (int) date('Y');
        }

        return [$year, $month];
    }

    private function monthStr(int $year, int $month): string
    {
        $months = [1 => 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];

        return ($months[$month] ?? $month) . ' ' . $year;
    }

    private function indexUrl(int $year, int $month): string
    {
        return site_url('admin/payroll') . '?month=' . sprintf('%04d-%02d', $year, $month);
    }

    private function detailUrl(int $id, int $year, int $month): string
    {
        return site_url('admin/payroll/' . $id) . '?month=' . sprintf('%04d-%02d', $year, $month);
    }
}
