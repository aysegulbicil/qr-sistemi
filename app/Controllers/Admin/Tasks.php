<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TaskModel;
use App\Models\UserModel;
use App\Services\NotificationService;

class Tasks extends BaseController
{
    public function index()
    {
        $userId = (int) ($this->request->getGet('user_id') ?: 0);
        $status = (string) ($this->request->getGet('status') ?: '');

        return view('admin/tasks/index', [
            'tasks'     => (new TaskModel())->listDetailed($userId ?: null, $status),
            'employees' => (new UserModel())->where('role', 'employee')->orderBy('full_name', 'ASC')->findAll(),
            'fUser'     => $userId,
            'fStatus'   => $status,
        ]);
    }

    public function new()
    {
        return view($this->wantsJson() ? 'admin/tasks/_form' : 'admin/tasks/form', $this->formData(null));
    }

    public function edit(int $id)
    {
        $task = (new TaskModel())->find($id);
        if ($task === null) {
            return redirect()->to('/admin/tasks')->with('error', 'Görev bulunamadı.');
        }

        return view($this->wantsJson() ? 'admin/tasks/_form' : 'admin/tasks/form', $this->formData($task));
    }

    public function create()
    {
        if ($error = $this->validatePayload()) {
            return $this->wantsJson() ? $this->jsonError($error) : redirect()->back()->withInput()->with('error', $error);
        }

        $model    = new TaskModel();
        $notifier = new NotificationService();
        $by       = (int) session()->get('user_id');
        $base     = $this->payload();
        $count    = 0;
        foreach ($this->selectedUsers() as $uid) {
            $model->insert($base + ['user_id' => $uid, 'assigned_by' => $by, 'status' => 'pending']);
            $notifier->notify($uid, 'task', 'Sana yeni görev atandı: ' . $base['title'], site_url('gorevlerim'));
            $count++;
        }

        $msg = $count > 1 ? ($count . ' personele görev atandı.') : 'Görev atandı.';

        return $this->wantsJson()
            ? $this->jsonOk(site_url('admin/tasks'), $msg)
            : redirect()->to('/admin/tasks')->with('message', $msg);
    }

    public function update(int $id)
    {
        $task = (new TaskModel())->find($id);
        if ($task === null) {
            $msg = 'Görev bulunamadı.';

            return $this->wantsJson() ? $this->jsonError($msg) : redirect()->to('/admin/tasks')->with('error', $msg);
        }
        if ($error = $this->validatePayload()) {
            return $this->wantsJson() ? $this->jsonError($error) : redirect()->back()->withInput()->with('error', $error);
        }

        $data  = $this->payload();
        $users = $this->selectedUsers();
        if ($users !== []) {
            $data['user_id'] = $users[0];
        }
        $status = (string) $this->request->getPost('status');
        if (in_array($status, TaskModel::STATUSES, true)) {
            $data['status']       = $status;
            $data['completed_at'] = $status === 'done' ? ($task['completed_at'] ?: date('Y-m-d H:i:s')) : null;
        }
        (new TaskModel())->update($id, $data);

        return $this->wantsJson()
            ? $this->jsonOk(site_url('admin/tasks'), 'Görev güncellendi.')
            : redirect()->to('/admin/tasks')->with('message', 'Görev güncellendi.');
    }

    public function delete(int $id)
    {
        (new TaskModel())->delete($id);

        return redirect()->to('/admin/tasks')->with('message', 'Görev silindi.');
    }

    // ---- yardımcılar ----

    private function formData(?array $task): array
    {
        return [
            'task'        => $task,
            'employees'   => (new UserModel())->where('role', 'employee')->orderBy('full_name', 'ASC')->findAll(),
            'prefillUser' => (int) ($this->request->getGet('user_id') ?: 0),
        ];
    }

    private function selectedUsers(): array
    {
        $raw = $this->request->getPost('user_id');
        $ids = is_array($raw) ? $raw : [$raw];

        return array_values(array_unique(array_filter(array_map('intval', $ids))));
    }

    private function validatePayload(): ?string
    {
        $title = trim((string) $this->request->getPost('title'));
        if ($title === '') {
            return 'Görev başlığı gerekli.';
        }
        if (mb_strlen($title) > 150) {
            return 'Başlık çok uzun (en fazla 150 karakter).';
        }
        if ($this->selectedUsers() === []) {
            return 'En az bir personel seç.';
        }
        if (! in_array((string) $this->request->getPost('priority'), TaskModel::PRIORITIES, true)) {
            return 'Öncelik geçersiz.';
        }
        $due = trim((string) $this->request->getPost('due_date'));
        if ($due !== '' && strtotime($due) === false) {
            return 'Son tarih geçersiz.';
        }

        return null;
    }

    private function payload(): array
    {
        $due = trim((string) $this->request->getPost('due_date'));

        return [
            'title'       => trim((string) $this->request->getPost('title')),
            'description' => trim((string) $this->request->getPost('description')) ?: null,
            'priority'    => (string) $this->request->getPost('priority'),
            'due_date'    => $due !== '' ? date('Y-m-d', strtotime($due)) : null,
        ];
    }
}
