<?php

namespace App\Services;

use App\Models\AttendanceLogModel;
use App\Models\UserModel;

/**
 * Builds tabular report datasets (title + columns + pre-formatted string rows),
 * usable for on-screen tables, CSV export and print views.
 */
class ReportService
{
    /** @return array{title:string,subtitle:string,columns:list<string>,rows:list<list<string>>} */
    public function build(string $type, string $start, string $end, ?int $deptId): array
    {
        return match ($type) {
            'late'     => $this->late($start, $end, $deptId),
            'overtime' => $this->overtime($start, $end, $deptId),
            'daily'    => $this->daily($start, $deptId),
            default    => $this->summary($start, $end, $deptId),
        };
    }

    public function types(): array
    {
        return [
            'summary'  => 'Devam özeti',
            'late'     => 'Geç kalanlar',
            'overtime' => 'Fazla mesai',
            'daily'    => 'Günlük hareketler',
        ];
    }

    private function employees(?int $deptId): array
    {
        $m = new UserModel();
        if ($deptId) {
            $m = $m->where('department_id', $deptId);
        }

        return $m->orderBy('full_name', 'ASC')->findAll();
    }

    private function aggregate(array $u, string $start, string $end): array
    {
        $logs   = (new AttendanceLogModel())->forUserBetween((int) $u['id'], $start, $end);
        $byDate = [];
        foreach ($logs as $l) {
            $byDate[substr($l['event_at'], 0, 10)][] = $l;
        }

        $shift    = (new ShiftResolver())->forUser((int) $u['id']);
        $workdays = array_map('strval', array_filter(explode(',', (string) ($shift['workdays'] ?? '1,2,3,4,5'))));
        $calc     = new AttendanceCalculator();
        $today    = date('Y-m-d');

        $late = 0; $ot = 0; $worked = 0; $present = 0; $expected = 0; $lateDays = 0;
        for ($d = strtotime($start); $d <= strtotime($end); $d = strtotime('+1 day', $d)) {
            $date = date('Y-m-d', $d);
            if (in_array(date('N', $d), $workdays, true) && $date <= $today) {
                $expected++;
            }
            $day = $calc->computeDay($byDate[$date] ?? [], $shift, $date);
            $late   += $day['late_minutes'];
            $ot     += $day['overtime_minutes'];
            $worked += $day['worked_minutes'];
            if (in_array($day['status'], ['present', 'incomplete'], true)) {
                $present++;
            }
            if ($day['late_minutes'] > 0) {
                $lateDays++;
            }
        }

        return compact('late', 'ot', 'worked', 'present', 'expected', 'lateDays') + ['missing' => max(0, $expected - $present)];
    }

    private function hm(int $m): string
    {
        $m = max(0, $m);
        $h = intdiv($m, 60);
        $r = $m % 60;

        return $h > 0 ? ($h . ' sa ' . $r . ' dk') : ($r . ' dk');
    }

    private function summary(string $start, string $end, ?int $deptId): array
    {
        $rows = [];
        foreach ($this->employees($deptId) as $u) {
            $a      = $this->aggregate($u, $start, $end);
            $rows[] = [
                (string) ($u['employee_code'] ?? ''),
                $u['full_name'],
                $a['present'] . ' / ' . $a['expected'],
                $this->hm($a['late']),
                $this->hm($a['ot']),
                (string) $a['missing'],
                $this->hm($a['worked']),
            ];
        }

        return ['title' => 'Devam özeti', 'subtitle' => $start . ' — ' . $end, 'columns' => ['Kod', 'Personel', 'Gün', 'Geç', 'Fazla mesai', 'Eksik gün', 'Çalışılan'], 'rows' => $rows];
    }

    private function late(string $start, string $end, ?int $deptId): array
    {
        $rows = [];
        foreach ($this->employees($deptId) as $u) {
            $a = $this->aggregate($u, $start, $end);
            if ($a['late'] > 0) {
                $rows[] = [(string) ($u['employee_code'] ?? ''), $u['full_name'], $this->hm($a['late']), (string) $a['lateDays']];
            }
        }
        usort($rows, static fn ($x, $y) => $y[3] <=> $x[3]);

        return ['title' => 'Geç kalanlar', 'subtitle' => $start . ' — ' . $end, 'columns' => ['Kod', 'Personel', 'Toplam geç', 'Geç gün'], 'rows' => $rows];
    }

    private function overtime(string $start, string $end, ?int $deptId): array
    {
        $rows = [];
        foreach ($this->employees($deptId) as $u) {
            $a = $this->aggregate($u, $start, $end);
            if ($a['ot'] > 0) {
                $rows[] = [(string) ($u['employee_code'] ?? ''), $u['full_name'], $this->hm($a['ot'])];
            }
        }

        return ['title' => 'Fazla mesai', 'subtitle' => $start . ' — ' . $end, 'columns' => ['Kod', 'Personel', 'Toplam fazla mesai'], 'rows' => $rows];
    }

    private function daily(string $date, ?int $deptId): array
    {
        $db = db_connect();
        $b  = $db->table('attendance_logs a')
            ->select('a.event_at, a.type, a.source, u.full_name, l.name AS loc')
            ->join('users u', 'u.id = a.user_id', 'left')
            ->join('locations l', 'l.id = a.location_id', 'left')
            ->where('a.event_at >=', $date . ' 00:00:00')
            ->where('a.event_at <=', $date . ' 23:59:59');
        if ($deptId) {
            $b->where('u.department_id', $deptId);
        }
        $logs = $b->orderBy('a.event_at', 'ASC')->get()->getResultArray();

        $srcMap = ['qr' => 'QR', 'manual' => 'Manuel', 'admin' => 'Yönetici'];
        $rows   = [];
        foreach ($logs as $l) {
            $rows[] = [
                substr($l['event_at'], 11, 5),
                $l['full_name'] ?: '—',
                $l['type'] === 'in' ? 'Giriş' : 'Çıkış',
                $l['loc'] ?: '—',
                $srcMap[$l['source']] ?? $l['source'],
            ];
        }

        return ['title' => 'Günlük hareketler', 'subtitle' => $date, 'columns' => ['Saat', 'Personel', 'Yön', 'Lokasyon', 'Kaynak'], 'rows' => $rows];
    }
}
