<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AttendanceLogModel;
use App\Models\DepartmentModel;
use App\Models\PositionModel;
use App\Models\ShiftModel;
use App\Models\UserModel;
use App\Services\AttendanceCalculator;
use App\Services\EmployeeService;
use App\Services\ShiftResolver;

class Employees extends BaseController
{
    public function index()
    {
        $req    = $this->request;
        $search = trim((string) $req->getGet('q'));
        $sort   = (string) ($req->getGet('sort') ?: 'full_name');
        $dir    = (string) ($req->getGet('dir') ?: 'asc');
        $dept   = $req->getGet('department_id') ? (int) $req->getGet('department_id') : null;
        $status = (string) ($req->getGet('status') ?: '');

        return view('admin/employees/index', [
            'rows'        => (new UserModel())->listDetailed($search, $sort, $dir, $dept, $status),
            'q'           => $search,
            'sort'        => $sort,
            'dir'         => $dir,
            'dept'        => $dept,
            'status'      => $status,
            'departments' => (new DepartmentModel())->ordered(),
        ]);
    }

    public function new()
    {
        return $this->form(null);
    }

    public function edit(int $id)
    {
        $employee = (new UserModel())->find($id);
        if ($employee === null) {
            return redirect()->to('/admin/employees')->with('error', 'Personel bulunamadı.');
        }

        return $this->form($employee);
    }

    private function form(?array $employee)
    {
        return view('admin/employees/form', [
            'employee'    => $employee,
            'departments' => (new DepartmentModel())->ordered(),
            'positions'   => (new PositionModel())->withDepartment(),
            'shifts'      => (new ShiftModel())->findAll(),
        ]);
    }

    public function create()
    {
        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        (new UserModel())->insert((new EmployeeService())->payloadFromRequest($this->request, false));

        return redirect()->to('/admin/employees')->with('message', 'Personel eklendi.');
    }

    public function update(int $id)
    {
        if (! $this->validate($this->rules($id))) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        (new UserModel())->update($id, (new EmployeeService())->payloadFromRequest($this->request, true));

        return redirect()->to('/admin/employees')->with('message', 'Personel güncellendi.');
    }

    public function show(int $id)
    {
        $emp = (new UserModel())->findDetailed($id);
        if ($emp === null) {
            return redirect()->to('/admin/employees')->with('error', 'Personel bulunamadı.');
        }

        // Bu ayki devam özeti
        $start = date('Y-m-01');
        $end   = date('Y-m-d');
        $logs  = (new AttendanceLogModel())->forUserBetween($id, $start, $end);

        $byDate = [];
        foreach ($logs as $l) {
            $byDate[substr($l['event_at'], 0, 10)][] = $l;
        }

        $shift = (new ShiftResolver())->forUser($id);
        $calc  = new AttendanceCalculator();
        $tLate = 0; $tOt = 0; $tWorked = 0; $present = 0;
        for ($d = strtotime($start); $d <= strtotime($end); $d = strtotime('+1 day', $d)) {
            $day = $calc->computeDay($byDate[date('Y-m-d', $d)] ?? [], $shift, date('Y-m-d', $d));
            $tLate += $day['late_minutes']; $tOt += $day['overtime_minutes']; $tWorked += $day['worked_minutes'];
            if (in_array($day['status'], ['present', 'incomplete'], true)) { $present++; }
        }

        return view('admin/employees/profile', [
            'emp'     => $emp,
            'tLate'   => $tLate,
            'tOt'     => $tOt,
            'tWorked' => $tWorked,
            'present' => $present,
            'recent'  => array_slice(array_reverse($logs), 0, 6),
        ]);
    }

    private function rules(?int $id = null): array
    {
        $unique = $id ? "is_unique[users.username,id,{$id}]" : 'is_unique[users.username]';

        return [
            'full_name'     => 'required|max_length[150]',
            'username'      => "required|max_length[100]|{$unique}",
            'password'      => $id ? 'permit_empty|min_length[4]' : 'required|min_length[4]',
            'salary_amount' => 'permit_empty|decimal',
            'contact_email' => 'permit_empty|valid_email',
        ];
    }
}
