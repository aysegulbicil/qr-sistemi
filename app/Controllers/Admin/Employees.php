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
        // Liste artık istemci taraflı DataTable ile yönetiliyor:
        // arama / sıralama / sayfalama / sayfa başı kayıt tarayıcıda yapılır.
        // Bu yüzden tüm satırları tek seferde döndürüyoruz.
        $users = new UserModel();

        return view('admin/employees/index', [
            'rows'        => $users->listDetailed('', 'full_name', 'asc', null, '', 1000000, 0),
            'departments' => (new DepartmentModel())->ordered(),
        ]);
    }

    /** Bulk actions on selected employees: status change or CSV export. */
    public function bulk()
    {
        $ids    = (array) $this->request->getPost('ids');
        $action = (string) $this->request->getPost('bulk_action');
        $ids    = array_values(array_filter(array_map('intval', $ids)));

        if ($ids === []) {
            return redirect()->to('/admin/employees')->with('error', 'Hiç personel seçilmedi.');
        }

        $users  = new UserModel();
        $labels = ['active' => 'Aktif', 'passive' => 'Pasif', 'terminated' => 'Ayrıldı'];

        if ($action === 'export') {
            $rows = $users->forExport($ids);
            $fh   = fopen('php://temp', 'r+');
            // CSV formul enjeksiyonu onlemi: =,+,-,@ ile baslayan hucreleri notrle.
            $csvSafe = static function ($v): string {
                $v = (string) $v;
                return preg_match('/^[=\-+@\t\r]/', $v) === 1 ? "'" . $v : $v;
            };
            fputcsv($fh, ['Ad Soyad', 'Personel kodu', 'Kullanıcı adı', 'Departman', 'Pozisyon', 'Durum']);
            foreach ($rows as $r) {
                fputcsv($fh, array_map($csvSafe, [
                    $r['full_name'] ?? '',
                    $r['employee_code'] ?? '',
                    $r['username'] ?? '',
                    $r['department_name'] ?? '',
                    $r['position_name'] ?? '',
                    $labels[$r['employment_status'] ?? ''] ?? ($r['employment_status'] ?? ''),
                ]));
            }
            rewind($fh);
            $csv = stream_get_contents($fh);
            fclose($fh);

            return $this->response
                ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
                ->setHeader('Content-Disposition', 'attachment; filename="personeller.csv"')
                ->setBody("\xEF\xBB\xBF" . $csv);
        }

        if (isset($labels[$action])) {
            $n = $users->bulkStatus($ids, $action);

            return redirect()->to('/admin/employees')->with('message', "{$n} personel durumu güncellendi: {$labels[$action]}.");
        }

        return redirect()->to('/admin/employees')->with('error', 'Geçersiz toplu işlem.');
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
        $data = [
            'employee'    => $employee,
            'departments' => (new DepartmentModel())->ordered(),
            'positions'   => (new PositionModel())->withDepartment(),
            'shifts'      => (new ShiftModel())->findAll(),
        ];

        return view($this->wantsJson() ? 'admin/employees/_form' : 'admin/employees/form', $data);
    }

    public function create()
    {
        if (! $this->validate($this->rules())) {
            $msg = implode(' ', $this->validator->getErrors());

            return $this->wantsJson() ? $this->jsonError($msg) : redirect()->back()->withInput()->with('error', $msg);
        }

        (new UserModel())->insert((new EmployeeService())->payloadFromRequest($this->request, false));

        return $this->wantsJson()
            ? $this->jsonOk(site_url('admin/employees'), 'Personel eklendi.')
            : redirect()->to('/admin/employees')->with('message', 'Personel eklendi.');
    }

    public function update(int $id)
    {
        if (! $this->validate($this->rules($id))) {
            $msg = implode(' ', $this->validator->getErrors());

            return $this->wantsJson() ? $this->jsonError($msg) : redirect()->back()->withInput()->with('error', $msg);
        }

        (new UserModel())->update($id, (new EmployeeService())->payloadFromRequest($this->request, true));

        return $this->wantsJson()
            ? $this->jsonOk(site_url('admin/employees'), 'Personel güncellendi.')
            : redirect()->to('/admin/employees')->with('message', 'Personel güncellendi.');
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
            'password'      => $id ? 'permit_empty|min_length[8]' : 'required|min_length[8]',
            'salary_amount' => 'permit_empty|decimal',
            'contact_email' => 'permit_empty|valid_email',
        ];
    }
}
