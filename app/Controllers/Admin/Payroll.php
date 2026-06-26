<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdvanceModel;
use App\Models\UserModel;
use App\Services\PayrollService;

class Payroll extends BaseController
{
    public function index()
    {
        [$year, $month] = $this->period();
        $svc            = new PayrollService();

        $rows  = [];
        $total = 0.0;
        foreach ((new UserModel())->employees() as $u) {
            $p      = $svc->computeMonth($u, $year, $month);
            $rows[] = ['user' => $u, 'p' => $p];
            $total += $p['net'];
        }

        return view('admin/payroll/index', [
            'rows'     => $rows,
            'year'     => $year,
            'month'    => $month,
            'ym'       => sprintf('%04d-%02d', $year, $month),
            'monthStr' => $this->monthStr($year, $month),
            'total'    => $total,
            'currency' => $rows[0]['p']['currency'] ?? '₺',
        ]);
    }

    public function show(int $id)
    {
        [$year, $month] = $this->period();
        $u              = (new UserModel())->findDetailed($id);
        if ($u === null) {
            return redirect()->to('/admin/payroll')->with('error', 'Personel bulunamadı.');
        }

        return view('admin/payroll/detail', [
            'u'        => $u,
            'p'        => (new PayrollService())->computeMonth($u, $year, $month),
            'advances' => (new AdvanceModel())->forUserPeriod($id, $year, $month),
            'year'     => $year,
            'month'    => $month,
            'ym'       => sprintf('%04d-%02d', $year, $month),
            'monthStr' => $this->monthStr($year, $month),
        ]);
    }

    public function addAdvance(int $id)
    {
        if (! $this->validate(['amount' => 'required|decimal|greater_than[0]'])) {
            return redirect()->back()->with('error', 'Geçerli bir tutar gir.');
        }

        [$year, $month] = $this->period();
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
        (new AdvanceModel())->delete($advId);
        [$year, $month] = $this->period();

        return redirect()->to($this->detailUrl($id, $year, $month))->with('message', 'Kayıt silindi.');
    }

    private function period(): array
    {
        $ym       = (string) ($this->request->getGet('month') ?: $this->request->getPost('month') ?: date('Y-m'));
        $parts    = array_pad(explode('-', $ym), 2, date('m'));
        $year     = (int) $parts[0];
        $month    = (int) $parts[1];
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

    private function detailUrl(int $id, int $year, int $month): string
    {
        return site_url('admin/payroll/' . $id) . '?month=' . sprintf('%04d-%02d', $year, $month);
    }
}
