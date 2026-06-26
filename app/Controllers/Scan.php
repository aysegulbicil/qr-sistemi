<?php

namespace App\Controllers;

use App\Models\LocationModel;
use App\Services\DynamicQr;

/**
 * Public entry point reached by scanning a QR code: /q/{location-code}
 * Detects whether the location uses a fixed or dynamic QR and validates accordingly,
 * then hands off to the (authenticated) dashboard where the user picks Check in / out.
 */
class Scan extends BaseController
{
    public function location(string $code)
    {
        $location = (new LocationModel())->findByCode(trim($code));

        if ($location === null) {
            return view('scan_error', [
                'message' => 'Bu QR adresi sistemde kayıtlı değil (okunan kod: "' . $code . '"). Yöneticiyle kontrol et.',
            ]);
        }
        if (! $location['is_active']) {
            return view('scan_error', [
                'message' => '"' . $location['name'] . '" lokasyonu şu an pasif. Yöneticinin aktif etmesi gerekiyor.',
            ]);
        }

        // Dynamic QR: a valid, unexpired, single-use token must be present.
        if (qr_effective_mode($location['qr_mode']) === 'dynamic') {
            $token = (string) $this->request->getGet('t');
            if ($token === '' || ! (new DynamicQr())->consume($token, (int) $location['id'])) {
                return view('scan_error', [
                    'message' => 'Bu QR kodunun süresi doldu. Ekrandaki güncel kodu tekrar okut.',
                ]);
            }
        }

        // Remember the validated scan so the dashboard can offer Check in / out.
        session()->set('scan_context', [
            'location_id'   => (int) $location['id'],
            'location_name' => $location['name'],
            'qr_mode'       => qr_effective_mode($location['qr_mode']),
            'at'            => date('Y-m-d H:i:s'),
        ]);

        // The auth filter sends guests to login first, then back here.
        return redirect()->to('/dashboard');
    }
}
