<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdvanceModel;
use App\Models\AdvanceRequestModel;
use App\Models\LeaveRequestModel;
use App\Services\NotificationService;

class Requests extends BaseController
{
    public function index()
    {
        return view('admin/requests/index', [
            'leaves'   => (new LeaveRequestModel())->allWithUser(),
            'advances' => (new AdvanceRequestModel())->allWithUser(),
        ]);
    }

    public function approveLeave(int $id)
    {
        return $this->decideLeave($id, 'approved');
    }

    public function rejectLeave(int $id)
    {
        return $this->decideLeave($id, 'rejected');
    }

    public function approveAdvance(int $id)
    {
        return $this->decideAdvance($id, 'approved');
    }

    public function rejectAdvance(int $id)
    {
        return $this->decideAdvance($id, 'rejected');
    }

    private function decideLeave(int $id, string $status)
    {
        $model = new LeaveRequestModel();
        $r     = $model->find($id);
        if ($r === null || $r['status'] !== 'pending') {
            return redirect()->to('/admin/requests')->with('error', 'Talep bulunamadı.');
        }

        $model->update($id, ['status' => $status, 'approver_id' => (int) session()->get('user_id'), 'decided_at' => date('Y-m-d H:i:s')]);
        $word = $status === 'approved' ? 'onaylandı' : 'reddedildi';
        (new NotificationService())->notify((int) $r['user_id'], 'leave', 'İzin talebin ' . $word . '.', site_url('requests'));

        return redirect()->to('/admin/requests')->with('message', 'İzin talebi ' . $word . '.');
    }

    private function decideAdvance(int $id, string $status)
    {
        $model = new AdvanceRequestModel();
        $r     = $model->find($id);
        if ($r === null || $r['status'] !== 'pending') {
            return redirect()->to('/admin/requests')->with('error', 'Talep bulunamadı.');
        }

        $model->update($id, ['status' => $status, 'approver_id' => (int) session()->get('user_id'), 'decided_at' => date('Y-m-d H:i:s')]);

        // Onaylanan avans, ilgili ayın puantajına (advances) yansır
        if ($status === 'approved') {
            (new AdvanceModel())->insert([
                'user_id'      => $r['user_id'],
                'type'         => 'advance',
                'amount'       => $r['amount'],
                'reason'       => 'Onaylı avans talebi',
                'period_year'  => $r['period_year'],
                'period_month' => $r['period_month'],
                'created_by'   => (int) session()->get('user_id'),
            ]);
        }

        $word = $status === 'approved' ? 'onaylandı' : 'reddedildi';
        (new NotificationService())->notify((int) $r['user_id'], 'advance', 'Avans talebin ' . $word . '.', site_url('requests'));

        return redirect()->to('/admin/requests')->with('message', 'Avans talebi ' . $word . '.');
    }
}
