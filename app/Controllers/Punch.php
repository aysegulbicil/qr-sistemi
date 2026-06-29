<?php

namespace App\Controllers;

/**
 * Tarama kapisi. Admin veya bu oturumda QR okutmus personeli panele (/dashboard) yollar;
 * okutmamis personele "Once QR okut" ekranini gosterir.
 */
class Punch extends BaseController
{
    public function index()
    {
        if (session()->get('role') === 'admin' || session()->get('scan_unlocked')) {
            return redirect()->to('/dashboard');
        }

        return view('punch', ['title' => 'QR Gerekli']);
    }
}
