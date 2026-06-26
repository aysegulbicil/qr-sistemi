<?php

namespace App\Services;

use App\Models\AdvanceModel;
use App\Models\AttendanceLogModel;
use App\Models\LeaveRequestModel;
use App\Models\SettingModel;

/**
 * Computes a monthly attendance + salary breakdown for a single employee (no SGK / tax v1).
 */
class PayrollService
{
    public function computeMonth(array $user, int $year, int $month): array
    {
        $settings   = (new SettingModel())->allMerged();
        $dailyHours = (float) ($settings['daily_hours'] ?? 9);
        $perMonth   = (float) ($settings['workdays_per_month'] ?? 22);
        $otMult     = (float) ($settings['overtime_multiplier'] ?? 1.5);
        $currency   = $settings['currency'] ?? '₺';

        $userId = (int) $user['id'];
        $start  = sprintf('%04d-%02d-01', $year, $month);
        $end    = date('Y-m-t', strtotime($start));
        $today  = date('Y-m-d');

        $logs   = (new AttendanceLogModel())->forUserBetween($userId, $start, $end);
        $byDate = [];
        foreach ($logs as $l) {
            $byDate[substr($l['event_at'], 0, 10)][] = $l;
        }

        $leaveDates = (new LeaveRequestModel())->approvedDates($userId, $start, $end);
        $shift      = (new ShiftResolver())->forUser($userId);
        $workdays   = array_map('strval', array_filter(explode(',', (string) ($shift['workdays'] ?? '1,2,3,4,5'))));
        $calc       = new AttendanceCalculator();

        $worked = 0; $late = 0; $ot = 0; $early = 0; $present = 0; $expected = 0; $leaveDays = 0;
        for ($d = strtotime($start); $d <= strtotime($end); $d = strtotime('+1 day', $d)) {
            $date = date('Y-m-d', $d);
            $iso  = date('N', $d);
            $isWorkday = in_array($iso, $workdays, true) && $date <= $today;

            if ($isWorkday) {
                if (isset($leaveDates[$date])) {
                    $leaveDays++;
                } else {
                    $expected++;
                }
            }

            $day = $calc->computeDay($byDate[$date] ?? [], $shift, $date);
            $worked += $day['worked_minutes'];
            $late   += $day['late_minutes'];
            $ot     += $day['overtime_minutes'];
            $early  += $day['early_leave_minutes'];
            if (in_array($day['status'], ['present', 'incomplete'], true)) {
                $present++;
            }
        }

        $missing     = max(0, $expected - $present);
        $workedHours = $worked / 60;

        $amount = (float) ($user['salary_amount'] ?? 0);
        $type   = $user['salary_type'] ?? 'monthly';
        if ($type === 'hourly') {
            $hourly = $amount;
            $base   = $workedHours * $hourly;
        } elseif ($type === 'daily') {
            $hourly = $dailyHours > 0 ? $amount / $dailyHours : 0;
            $base   = $present * $amount;
        } else {
            $hourly = ($perMonth * $dailyHours) > 0 ? $amount / ($perMonth * $dailyHours) : 0;
            $base   = $amount;
        }

        $otPay = ($ot / 60) * $hourly * $otMult;

        $tot      = (new AdvanceModel())->totals($userId, $year, $month);
        $advTotal = $tot['advance'];
        $dedTotal = $tot['deduction'];
        $net      = $base + $otPay - $advTotal - $dedTotal;

        return [
            'worked_minutes'   => $worked,
            'late_minutes'     => $late,
            'overtime_minutes' => $ot,
            'early_minutes'    => $early,
            'present_days'     => $present,
            'expected_days'    => $expected,
            'missing_days'     => $missing,
            'leave_days'       => $leaveDays,
            'worked_hours'     => $workedHours,
            'base'             => $base,
            'hourly'           => $hourly,
            'overtime_pay'     => $otPay,
            'advances_total'   => $advTotal,
            'deductions_total' => $dedTotal,
            'net'              => $net,
            'salary_type'      => $type,
            'salary_amount'    => $amount,
            'currency'         => $currency,
            'overtime_mult'    => $otMult,
        ];
    }
}
