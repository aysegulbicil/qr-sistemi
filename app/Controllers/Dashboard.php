<?php

namespace App\Controllers;

use App\Models\AttendanceLogModel;
use App\Services\AttendanceCalculator;
use App\Services\ShiftResolver;

class Dashboard extends BaseController
{
    public function index()
    {
        $userId = (int) session()->get('user_id');
        $model  = new AttendanceLogModel();
        $today  = date('Y-m-d');

        $todayLogs = $model->forUserBetween($userId, $today, $today);
        $shift     = (new ShiftResolver())->forUser($userId);
        $summary   = (new AttendanceCalculator())->computeDay($todayLogs, $shift, $today);

        return view('dashboard', [
            'checkedIn' => $model->isCheckedIn($userId),
            'scan'      => session()->get('scan_context'),
            'today'     => $summary,
        ]);
    }
}
