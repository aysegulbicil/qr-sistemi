<?php

namespace App\Controllers;

use App\Models\AttendanceLogModel;
use App\Services\AttendanceCalculator;
use App\Services\ShiftResolver;

class History extends BaseController
{
    private const DAYS = 14;

    public function index()
    {
        $userId = (int) session()->get('user_id');

        $end   = date('Y-m-d');
        $start = date('Y-m-d', strtotime('-' . (self::DAYS - 1) . ' days'));

        $logs = (new AttendanceLogModel())->forUserBetween($userId, $start, $end);

        $byDate = [];
        foreach ($logs as $log) {
            $byDate[substr($log['event_at'], 0, 10)][] = $log;
        }

        $shift = (new ShiftResolver())->forUser($userId);
        $calc  = new AttendanceCalculator();

        $days = [];
        for ($i = 0; $i < self::DAYS; $i++) {
            $date        = date('Y-m-d', strtotime('-' . $i . ' days'));
            $days[$date] = $calc->computeDay($byDate[$date] ?? [], $shift, $date);
        }

        return view('history', ['days' => $days]);
    }
}
