<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DepartmentModel;

class Departments extends BaseController
{
    public function index()
    {
        return view('admin/departments/index', [
            'departments' => (new DepartmentModel())->ordered(),
        ]);
    }

    public function new()
    {
        return view('admin/departments/form', ['department' => null]);
    }

    public function create()
    {
        if (! $this->validate(['name' => 'required|max_length[120]'])) {
            return redirect()->back()->withInput()->with('error', 'Departman adı gerekli.');
        }

        (new DepartmentModel())->insert([
            'name'        => (string) $this->request->getPost('name'),
            'description' => $this->request->getPost('description') ?: null,
        ]);

        return redirect()->to('/admin/departments')->with('message', 'Departman eklendi.');
    }

    public function edit(int $id)
    {
        $department = (new DepartmentModel())->find($id);
        if ($department === null) {
            return redirect()->to('/admin/departments')->with('error', 'Departman bulunamadı.');
        }

        return view('admin/departments/form', ['department' => $department]);
    }

    public function update(int $id)
    {
        if (! $this->validate(['name' => 'required|max_length[120]'])) {
            return redirect()->back()->withInput()->with('error', 'Departman adı gerekli.');
        }

        (new DepartmentModel())->update($id, [
            'name'        => (string) $this->request->getPost('name'),
            'description' => $this->request->getPost('description') ?: null,
        ]);

        return redirect()->to('/admin/departments')->with('message', 'Departman güncellendi.');
    }

    public function delete(int $id)
    {
        (new DepartmentModel())->delete($id);

        return redirect()->to('/admin/departments')->with('message', 'Departman silindi.');
    }
}
