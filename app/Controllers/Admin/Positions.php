<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DepartmentModel;
use App\Models\PositionModel;

class Positions extends BaseController
{
    public function index()
    {
        return view('admin/positions/index', [
            'positions' => (new PositionModel())->withDepartment(),
        ]);
    }

    public function new()
    {
        return view($this->wantsJson() ? 'admin/positions/_form' : 'admin/positions/form', [
            'position'    => null,
            'departments' => (new DepartmentModel())->ordered(),
        ]);
    }

    public function create()
    {
        if (! $this->validate(['name' => 'required|max_length[120]'])) {
            $msg = 'Pozisyon adı gerekli.';

            return $this->wantsJson() ? $this->jsonError($msg) : redirect()->back()->withInput()->with('error', $msg);
        }

        (new PositionModel())->insert([
            'name'          => (string) $this->request->getPost('name'),
            'department_id' => $this->request->getPost('department_id') ?: null,
            'description'   => $this->request->getPost('description') ?: null,
        ]);

        return $this->wantsJson()
            ? $this->jsonOk(site_url('admin/positions'), 'Pozisyon eklendi.')
            : redirect()->to('/admin/positions')->with('message', 'Pozisyon eklendi.');
    }

    public function edit(int $id)
    {
        $position = (new PositionModel())->find($id);
        if ($position === null) {
            return redirect()->to('/admin/positions')->with('error', 'Pozisyon bulunamadı.');
        }

        return view($this->wantsJson() ? 'admin/positions/_form' : 'admin/positions/form', [
            'position'    => $position,
            'departments' => (new DepartmentModel())->ordered(),
        ]);
    }

    public function update(int $id)
    {
        if (! $this->validate(['name' => 'required|max_length[120]'])) {
            $msg = 'Pozisyon adı gerekli.';

            return $this->wantsJson() ? $this->jsonError($msg) : redirect()->back()->withInput()->with('error', $msg);
        }

        (new PositionModel())->update($id, [
            'name'          => (string) $this->request->getPost('name'),
            'department_id' => $this->request->getPost('department_id') ?: null,
            'description'   => $this->request->getPost('description') ?: null,
        ]);

        return $this->wantsJson()
            ? $this->jsonOk(site_url('admin/positions'), 'Pozisyon güncellendi.')
            : redirect()->to('/admin/positions')->with('message', 'Pozisyon güncellendi.');
    }

    public function delete(int $id)
    {
        (new PositionModel())->delete($id);

        return redirect()->to('/admin/positions')->with('message', 'Pozisyon silindi.');
    }
}
