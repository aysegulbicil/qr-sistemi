<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ShiftModel;

class Shifts extends BaseController
{
    public function index()
    {
        return view('admin/shifts/index', ['shifts' => (new ShiftModel())->ordered()]);
    }

    public function new()
    {
        return view('admin/shifts/form', ['shift' => null]);
    }

    public function edit(int $id)
    {
        $shift = (new ShiftModel())->find($id);
        if ($shift === null) {
            return redirect()->to('/admin/shifts')->with('error', 'Vardiya bulunamadı.');
        }

        return view('admin/shifts/form', ['shift' => $shift]);
    }

    public function create()
    {
        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }
        (new ShiftModel())->insert($this->payload());

        return redirect()->to('/admin/shifts')->with('message', 'Vardiya eklendi.');
    }

    public function update(int $id)
    {
        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }
        (new ShiftModel())->update($id, $this->payload());

        return redirect()->to('/admin/shifts')->with('message', 'Vardiya güncellendi.');
    }

    public function delete(int $id)
    {
        (new ShiftModel())->delete($id);

        return redirect()->to('/admin/shifts')->with('message', 'Vardiya silindi.');
    }

    private function payload(): array
    {
        $start = (string) $this->request->getPost('start_time');
        $end   = (string) $this->request->getPost('end_time');
        $days  = $this->request->getPost('workdays');
        $days  = is_array($days) ? array_values(array_filter($days, static fn ($d) => $d >= 1 && $d <= 7)) : [];

        return [
            'name'              => trim((string) $this->request->getPost('name')),
            'start_time'        => $start,
            'end_time'          => $end,
            'grace_in_minutes'  => (int) $this->request->getPost('grace_in_minutes'),
            'grace_out_minutes' => (int) $this->request->getPost('grace_out_minutes'),
            'crosses_midnight'  => ($end !== '' && $start !== '' && $end <= $start) ? 1 : 0,
            'workdays'          => implode(',', $days),
        ];
    }

    private function rules(): array
    {
        return [
            'name'       => 'required|max_length[100]',
            'start_time' => 'required',
            'end_time'   => 'required',
        ];
    }
}
