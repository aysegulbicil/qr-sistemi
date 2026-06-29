<?php

namespace App\Controllers;

use App\Models\LocationModel;
use App\Services\DynamicQr;

/**
 * Panel-disi, imzali, salt-gosterim KAPI EKRANI (kiosk).
 * URL: /display/{id}/{token_secret}. Admin oturumu TASIMAZ; kapi cihazi
 * yalnizca bu sayfayi acar. Dinamik modda rotasyon tokenlari buradan doner.
 */
class Display extends BaseController
{
    public function screen(int $id, string $secret)
    {
        $location = $this->authorize($id, $secret);
        if ($location === null) {
            return $this->response->setStatusCode(403)->setBody($this->forbiddenHtml());
        }

        return view('display/screen', [
            'location' => $location,
            'mode'     => qr_effective_mode($location['qr_mode']),
            'tokenUrl' => site_url('display/' . $location['id'] . '/' . $location['token_secret'] . '/token'),
        ]);
    }

    public function token(int $id, string $secret)
    {
        $location = $this->authorize($id, $secret);
        if ($location === null) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'forbidden']);
        }

        $base = rtrim(base_url(), '/') . '/q/' . $location['code'];

        if (qr_effective_mode($location['qr_mode']) === 'dynamic') {
            $token = (new DynamicQr())->issue((int) $location['id']);

            return $this->response->setJSON(['url' => $base . '?t=' . $token, 'ttl' => DynamicQr::TTL_SECONDS]);
        }

        return $this->response->setJSON(['url' => $base, 'ttl' => 0]);
    }

    private function authorize(int $id, string $secret): ?array
    {
        $location = (new LocationModel())->find($id);
        if ($location === null || empty($location['token_secret']) || ! $location['is_active']) {
            return null;
        }

        return hash_equals((string) $location['token_secret'], $secret) ? $location : null;
    }

    private function forbiddenHtml(): string
    {
        return '<!doctype html><meta charset="utf-8"><title>Geçersiz</title>'
            . '<div style="font-family:system-ui,sans-serif;display:grid;place-items:center;min-height:100vh;margin:0;color:#334155">'
            . '<div style="text-align:center"><h1 style="margin:0 0 8px">Geçersiz ekran linki</h1>'
            . '<p>Bu kapı ekranı linki geçersiz ya da lokasyon pasif. Yöneticiyle kontrol et.</p></div></div>';
    }
}
