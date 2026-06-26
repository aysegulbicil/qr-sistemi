<?php

namespace App\Services;

/**
 * Computes lateness / overtime / worked time for a single user on a single day,
 * from that day's ordered attendance events and the applicable shift.
 */
class AttendanceCalculator
{
    /**
     * @param array  $logs  Ascending attendance_logs rows for ONE user on ONE day.
     * @param array  $shift start_time, end_time, grace_in_minutes, grace_out_minutes, crosses_midnight
     * @param string $date  'Y-m-d'
     */
    public function computeDay(array $logs, array $shift, string $date): array
    {
        $firstIn = null;
        $lastOut = null;

        foreach ($logs as $log) {
            if ($log['type'] === 'in' && $firstIn === null) {
                $firstIn = $log['event_at'];
            }
            if ($log['type'] === 'out') {
                $lastOut = $log['event_at'];
            }
        }

        $result = [
            'date'                => $date,
            'first_in'            => $firstIn,
            'last_out'            => $lastOut,
            'late_minutes'        => 0,
            'overtime_minutes'    => 0,
            'early_leave_minutes' => 0,
            'worked_minutes'      => 0,
            'status'              => 'absent',
        ];

        if ($firstIn === null) {
            return $result;
        }

        $shiftStart = strtotime($date . ' ' . $shift['start_time']);
        $shiftEnd   = strtotime($date . ' ' . $shift['end_time']);
        if (! empty($shift['crosses_midnight']) && $shiftEnd <= $shiftStart) {
            $shiftEnd = strtotime($date . ' ' . $shift['end_time'] . ' +1 day');
        }

        $graceIn  = (int) ($shift['grace_in_minutes'] ?? 0) * 60;
        $graceOut = (int) ($shift['grace_out_minutes'] ?? 0) * 60;

        $inTs                   = strtotime($firstIn);
        $result['late_minutes'] = max(0, (int) floor(($inTs - ($shiftStart + $graceIn)) / 60));

        if ($lastOut !== null) {
            $outTs = strtotime($lastOut);

            $result['overtime_minutes']    = max(0, (int) floor(($outTs - ($shiftEnd + $graceOut)) / 60));
            $result['early_leave_minutes'] = max(0, (int) floor(($shiftEnd - $outTs) / 60));
            $result['worked_minutes']      = max(0, (int) floor(($outTs - $inTs) / 60));
            $result['status']              = 'present';
        } else {
            $result['status'] = 'incomplete'; // checked in, never checked out
        }

        return $result;
    }
}
