<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SuspiciousEventModel;

class Suspicious extends BaseController
{
    public function index()
    {
        return view('admin/suspicious/index', [
            'events' => (new SuspiciousEventModel())->recent(200),
        ]);
    }
}
