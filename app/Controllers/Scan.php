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
            return $this->fail('Bu QR adresi sistemde kayıtlı değil (okunan kod: "' . $code . '"). Yöneticiyle kontrol et.');
        }
        if (! $location['is_active']) {
            return $this->fail('"' . $location['name'] . '" lokasyonu şu an pasif. Yöneticinin aktif etmesi gerekiyor.');
        }

        // Dinamik QR: gecerli, suresi dolmamis token GET ile DOGRULANIR ama tuketilmez.
        // Tuketim punch (POST) aninda yapilir; boylece link onizlemesi tokeni yakmaz.
        $tokenId = null;
        if (qr_effective_mode($location['qr_mode']) === 'dynamic') {
            $token = trim((string) $this->request->getGet('t'));
            $row   = $token === '' ? null : (new DynamicQr())->validate($token, (int) $location['id']);
            if ($row === null) {
                $reason = $token === '' ? 'missing' : (new DynamicQr())->failureReason($token, (int) $location['id']);
                log_message('warning', 'QR tarama reddedildi: loc=' . $location['code'] . ' reason=' . $reason);

                return $this->fail('Bu QR kodunun süresi doldu. Ekrandaki güncel kodu tekrar okut.');
            }
            $tokenId = (int) $row['id'];
        }

        // Dogrulanan taramayi hatirla; punch ekrani giris/cikis sunar, token POST'ta tuketilir.
        session()->set('scan_context', [
            'location_id'   => (int) $location['id'],
            'location_name' => $location['name'],
            'qr_mode'       => qr_effective_mode($location['qr_mode']),
            'qr_token_id'   => $tokenId,
            'at'            => date('Y-m-d H:i:s'),
        ]);
        // Personel icin: bu login oturumu QR ile acildi (panel erisimi); cikisa kadar gecerli.
        session()->set('scan_unlocked', true);

        // The auth filter sends guests to login first, then back here.
        return redirect()->to('/dashboard');
    }

    /** Hata ekrani: bayat tarama baglamini temizle ki uyari sonrasi punch mumkun olmasin. */
    private function fail(string $message)
    {
        session()->remove('scan_context');

        return view('scan_error', ['message' => $message]);
    }
}
