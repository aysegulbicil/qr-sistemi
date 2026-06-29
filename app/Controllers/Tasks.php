<?php

namespace App\Controllers;

use App\Models\TaskModel;

/**
 * Personelin kendi görevleri: listele + durum güncelle (kendi görevinde).
 */
class Tasks extends BaseController
{
    public function mine()
    {
        $uid = (int) session()->get('user_id');

        return view('tasks/mine', ['tasks' => (new TaskModel())->forUser($uid)]);
    }

    public function updateStatus(int $id)
    {
        $status = (string) $this->request->getPost('status');
        if (! in_array($status, ['pending', 'in_progress', 'done'], true)) {
            return redirect()->to('/gorevlerim')->with('error', 'Geçersiz durum.');
        }

        $ok = (new TaskModel())->markStatus($id, $status, (int) session()->get('user_id'));

        return redirect()->to('/gorevlerim')->with($ok ? 'message' : 'error', $ok ? 'Görev güncellendi.' : 'Görev bulunamadı.');
    }
}
