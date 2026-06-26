<?php

namespace App\Controllers;

use App\Models\AttendanceLogModel;
use App\Models\LocationModel;
use App\Models\SuspiciousEventModel;
use App\Services\GeoService;

class Attendance extends BaseController
{
    private const DEFAULT_RADIUS_M = 150;

    public function punch()
    {
        $userId = (int) session()->get('user_id');
        $type   = (string) $this->request->getPost('type'); // 'in' | 'out'

        if (! in_array($type, ['in', 'out'], true)) {
            return redirect()->to('/dashboard')->with('error', 'Geçersiz işlem.');
        }

        $context = session()->get('scan_context');
        if (empty($context['location_id'])) {
            return redirect()->to('/dashboard')->with('error', 'Önce kapıdaki QR kodunu okutmalısın.');
        }
        if (! scan_is_fresh($context)) {
            session()->remove('scan_context');
            return redirect()->to('/dashboard')->with('error', 'Okutmanın süresi doldu. Kapıdaki QR kodunu tekrar okut.');
        }

        $model     = new AttendanceLogModel();
        $checkedIn = $model->isCheckedIn($userId);

        // Otomatik yön + aynı gün mükerrer kontrolü (kişiye bağlı)
        if ($type === 'in' && $checkedIn) {
            return redirect()->to('/dashboard')->with('error', 'Zaten giriş yaptın. Önce çıkış yapmalısın.');
        }
        if ($type === 'out' && ! $checkedIn) {
            return redirect()->to('/dashboard')->with('error', 'Henüz giriş yapmadın.');
        }

        $context    = session()->get('scan_context') ?? [];
        $locationId = $context['location_id'] ?? null;

        $userLat = $this->request->getPost('geo_lat');
        $userLng = $this->request->getPost('geo_lng');
        $userLat = is_numeric($userLat) ? (float) $userLat : null;
        $userLng = is_numeric($userLng) ? (float) $userLng : null;
        $distance = null;

        // Geofence kontrolü (lokasyon zorunlu kılıyorsa)
        if ($locationId) {
            $loc = (new LocationModel())->find($locationId);
            if ($loc && $loc['enforce_geo'] && $loc['geo_lat'] !== null && $loc['geo_lng'] !== null) {
                if ($userLat === null || $userLng === null) {
                    $this->logSuspicious($userId, $locationId, $type, 'Konum alınamadı', null, null, null);
                    return redirect()->to('/dashboard')->with('error', 'Konum alınamadı. Lütfen tarayıcı konum iznini aç ve tekrar dene.');
                }

                $distance = (int) round((new GeoService())->distanceMeters((float) $loc['geo_lat'], (float) $loc['geo_lng'], $userLat, $userLng));
                $radius   = (int) ($loc['geo_radius_m'] ?: self::DEFAULT_RADIUS_M);

                if ($distance > $radius) {
                    $this->logSuspicious($userId, $locationId, $type, 'Konum dışı işlem', $userLat, $userLng, $distance);
                    return redirect()->to('/dashboard')->with('error', 'Konumun iş yeri alanı dışında (' . $distance . ' m). İşlem reddedildi.');
                }
            } elseif ($loc && $userLat !== null && $loc['geo_lat'] !== null) {
                $distance = (int) round((new GeoService())->distanceMeters((float) $loc['geo_lat'], (float) $loc['geo_lng'], $userLat, $userLng));
            }
        }

        $model->insert([
            'user_id'     => $userId,
            'location_id' => $locationId,
            'type'        => $type,
            'event_at'    => date('Y-m-d H:i:s'),
            'source'      => isset($context['location_id']) ? 'qr' : 'manual',
            'ip_address'  => $this->request->getIPAddress(),
            'user_agent'  => substr((string) $this->request->getUserAgent(), 0, 255),
            'geo_lat'     => $userLat,
            'geo_lng'     => $userLng,
            'distance_m'  => $distance,
        ]);

        session()->remove('scan_context');

        $label = $type === 'in' ? 'Giriş' : 'Çıkış';

        return redirect()->to('/dashboard')->with('message', $label . ' kaydedildi · ' . date('H:i'));
    }

    private function logSuspicious(int $userId, ?int $locationId, string $type, string $reason, ?float $lat, ?float $lng, ?int $distance): void
    {
        (new SuspiciousEventModel())->insert([
            'user_id'     => $userId,
            'location_id' => $locationId,
            'type'        => $type,
            'reason'      => $reason,
            'geo_lat'     => $lat,
            'geo_lng'     => $lng,
            'distance_m'  => $distance,
            'ip_address'  => $this->request->getIPAddress(),
        ]);
    }
}
