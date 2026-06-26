<?php

namespace App\Controllers;

use App\Models\NotificationModel;

class Notifications extends BaseController
{
    public function index()
    {
        $uid   = (int) session()->get('user_id');
        $model = new NotificationModel();
        $list  = $model->forUser($uid);
        $model->markAllRead($uid);

        return view('notifications/index', ['list' => $list]);
    }
}
