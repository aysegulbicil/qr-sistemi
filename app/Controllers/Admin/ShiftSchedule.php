<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ShiftAssignmentModel;
use App\Models\ShiftModel;
use App\Models\UserModel;

class ShiftSchedule extends BaseController
{
    public function index()
    {
        $userId    = (int) ($this->request->getGet('user_id') ?: 0);
        $weekStart = (string) ($this->request->getGet('week') ?: date('Y-m-d', strtotime('monday this week')));

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $days[] = date('Y-m-d', strtotime($weekStart . ' +' . $i . ' day'));
        }

        $assignments = $userId ? (new ShiftAssignmentModel())->forUserBetween($userId, $days[0], $days[6]) : [];

        return view('admin/shifts/schedule', [
            'employees'   => (new UserModel())->employees(),
            'shifts'      => (new ShiftModel())->ordered(),
            'userId'      => $userId,
            'weekStart'   => $weekStart,
            'days'        => $days,
            'assignments' => $assignments,
            'prevWeek'    => date('Y-m-d', strtotime($weekStart . ' -7 day')),
            'nextWeek'    => date('Y-m-d', strtotime($weekStart . ' +7 day')),
        ]);
    }

    public function save()
    {
        $userId = (int) $this->request->getPost('user_id');
        $week   = (string) $this->request->getPost('week');
        $shifts = $this->request->getPost('shift');

        if ($userId && is_array($shifts)) {
            $model = new ShiftAssignmentModel();
            foreach ($shifts as $date => $shiftId) {
                if ($shiftId) {
                    $model->assign($userId, $date, (int) $shiftId);
                }
            }
        }

        return redirect()->to(site_url('admin/shift-schedule') . '?user_id=' . $userId . '&week=' . $week)
            ->with('message', 'Vardiya planı kaydedildi.');
    }
}
