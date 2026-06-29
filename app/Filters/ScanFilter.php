<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Personel (admin olmayan) panele girmeden once, bu login oturumunda en az bir kez
 * QR okutmus olmalidir. Basarili tarama Scan::location icinde session('scan_unlocked')
 * bayragini set eder; cikista (session destroy) silinir.
 *
 * - Giris yapmamis kullaniciyi AuthFilter login'e yollar (burada dokunmayiz).
 * - Admin muaftir (URL ile erisebilir).
 * - Tarama yapmamis personel /punch'a ("Once QR okut") yonlendirilir.
 */
class ScanFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('user_id')) {
            return; // AuthFilter halledecek
        }
        if (session()->get('role') === 'admin') {
            return; // admin muaf
        }
        if (! session()->get('scan_unlocked')) {
            return redirect()->to('/punch');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
