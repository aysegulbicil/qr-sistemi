<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdvanceRequestModel;
use App\Models\AttendanceLogModel;
use App\Models\LeaveRequestModel;
use App\Models\UserModel;
use App\Services\AttendanceCalculator;
use App\Services\ShiftResolver;

class Home extends BaseController
{
    public function index()
    {
        $today    = date('Y-m-d');
        $users    = (new UserModel())->employees();
        $logModel = new AttendanceLogModel();
        $calc     = new AttendanceCalculator();
        $resolver = new ShiftResolver();
        $db       = db_connect();

        // ---- Bugünkü giriş/çıkış özetleri + geç/erken listeleri ----
        $inManual = 0; $inQr = 0; $totalIn = 0;
        $outManual = 0; $outQr = 0; $totalOut = 0;
        $lateCount = 0; $earlyCount = 0;
        $activeCount = 0; $presentCount = 0;
        $lateList = []; $earlyList = [];

        foreach ($users as $u) {
            if ($u['is_active']) {
                $activeCount++;
            }
            $logs = $logModel->forUserBetween((int) $u['id'], $today, $today);
            if (! $logs) {
                continue;
            }

            $hasIn = false;
            foreach ($logs as $l) {
                if ($l['type'] === 'in') {
                    $hasIn = true; $totalIn++;
                    $l['source'] === 'manual' ? $inManual++ : $inQr++;
                } else {
                    $totalOut++;
                    $l['source'] === 'manual' ? $outManual++ : $outQr++;
                }
            }
            if ($hasIn) {
                $presentCount++;
            }

            $day = $calc->computeDay($logs, $resolver->forUser((int) $u['id']), $today);
            if ($day['late_minutes'] > 0) {
                $lateCount++;
                $lateList[] = ['name' => $u['full_name'], 'code' => $u['employee_code'], 'time' => $day['first_in'], 'mins' => $day['late_minutes']];
            }
            if ($day['early_leave_minutes'] > 0) {
                $earlyCount++;
                $earlyList[] = ['name' => $u['full_name'], 'code' => $u['employee_code'], 'time' => $day['last_out'], 'mins' => $day['early_leave_minutes']];
            }
        }
        usort($lateList, static fn ($a, $b) => $b['mins'] <=> $a['mins']);
        usort($earlyList, static fn ($a, $b) => $b['mins'] <=> $a['mins']);

        // ---- Haftalık giriş/çıkış grafiği (son 7 gün) ----
        $weekStart = date('Y-m-d', strtotime('-6 days'));
        $grouped   = $db->table('attendance_logs')
            ->select('DATE(event_at) AS d, type, COUNT(*) AS c')
            ->where('event_at >=', $weekStart . ' 00:00:00')
            ->groupBy('d, type')
            ->get()->getResultArray();

        $inBy = []; $outBy = [];
        foreach ($grouped as $g) {
            if ($g['type'] === 'in') {
                $inBy[$g['d']] = (int) $g['c'];
            } else {
                $outBy[$g['d']] = (int) $g['c'];
            }
        }
        $short  = ['Paz', 'Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt'];
        $labels = []; $inSeries = []; $outSeries = [];
        for ($i = 6; $i >= 0; $i--) {
            $dt        = date('Y-m-d', strtotime('-' . $i . ' days'));
            $labels[]  = $short[(int) date('w', strtotime($dt))] . ' ' . date('d', strtotime($dt));
            $inSeries[]  = $inBy[$dt] ?? 0;
            $outSeries[] = $outBy[$dt] ?? 0;
        }

        // ---- Son hareketler ----
        $recent = $db->table('attendance_logs a')
            ->select('a.event_at, a.type, u.full_name')
            ->join('users u', 'u.id = a.user_id', 'left')
            ->orderBy('a.event_at', 'DESC')
            ->limit(8)
            ->get()->getResultArray();

        // ---- Bekleyen talepler ----
        $pendingLeaves   = (new LeaveRequestModel())->pending();
        $pendingAdvances = (new AdvanceRequestModel())->pending();

        return view('admin/home', [
            'title'          => 'Genel Bakış',
            'inManual'       => $inManual, 'inQr' => $inQr, 'totalIn' => $totalIn,
            'outManual'      => $outManual, 'outQr' => $outQr, 'totalOut' => $totalOut,
            'lateCount'      => $lateCount, 'earlyCount' => $earlyCount,
            'lateList'       => $lateList, 'earlyList' => $earlyList,
            'activeCount'    => $activeCount, 'presentCount' => $presentCount,
            'absentCount'    => max(0, $activeCount - $presentCount),
            'totalEmployees' => count($users),
            'chartLabels'    => json_encode($labels),
            'chartIn'        => json_encode($inSeries),
            'chartOut'       => json_encode($outSeries),
            'recent'         => $recent,
            'pendingLeaves'  => $pendingLeaves,
            'pendingAdvances'=> $pendingAdvances,
            'pendingTotal'   => count($pendingLeaves) + count($pendingAdvances),
        ]);
    }
}
