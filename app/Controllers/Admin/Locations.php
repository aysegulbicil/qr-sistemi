<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LocationModel;
use App\Services\DynamicQr;

class Locations extends BaseController
{
    public function index()
    {
        return view('admin/locations/index', [
            'locations'   => (new LocationModel())->orderBy('name', 'ASC')->findAll(),
            'limit'       => max_locations(),
            'activeCount' => $this->activeLocationCount(),
        ]);
    }

    public function new()
    {
        if ($error = $this->locationLimitError()) {
            return redirect()->to('/admin/locations')->with('error', $error);
        }

        return view($this->wantsJson() ? 'admin/locations/_form' : 'admin/locations/form', ['location' => null, 'regen' => $this->regenInfo()]);
    }

    public function edit(int $id)
    {
        $location = (new LocationModel())->find($id);
        if ($location === null) {
            return redirect()->to('/admin/locations')->with('error', 'Lokasyon bulunamadı.');
        }

        return view($this->wantsJson() ? 'admin/locations/_form' : 'admin/locations/form', ['location' => $location, 'regen' => $this->regenInfo()]);
    }

    public function create()
    {
        if ($error = $this->locationLimitError()) {
            return $this->wantsJson() ? $this->jsonError($error) : redirect()->to('/admin/locations')->with('error', $error);
        }

        $code = slugify_code((string) $this->request->getPost('code'));
        if ($error = $this->validateLocation($code, null)) {
            return $this->wantsJson() ? $this->jsonError($error) : redirect()->back()->withInput()->with('error', $error);
        }

        $data                 = $this->payload($code);
        $data['token_secret'] = $data['qr_mode'] === 'dynamic' ? bin2hex(random_bytes(16)) : null;
        $data['is_active']    = 1;
        (new LocationModel())->insert($data);

        $msg = 'Lokasyon eklendi. Kod: ' . $code;

        return $this->wantsJson() ? $this->jsonOk(site_url('admin/locations'), $msg) : redirect()->to('/admin/locations')->with('message', $msg);
    }

    public function update(int $id)
    {
        $code = slugify_code((string) $this->request->getPost('code'));
        if ($error = $this->validateLocation($code, $id)) {
            return $this->wantsJson() ? $this->jsonError($error) : redirect()->back()->withInput()->with('error', $error);
        }

        $existing          = (new LocationModel())->find($id);
        $data              = $this->payload($code);
        $data['is_active'] = $this->request->getPost('is_active') ? 1 : 0;

        // Lisans lokasyon limiti: PASIF bir lokasyon yeniden aktive ediliyorsa slot tuketir.
        if ($data['is_active'] === 1 && (int) ($existing['is_active'] ?? 0) === 0
            && ($error = $this->locationLimitError())) {
            return $this->wantsJson() ? $this->jsonError($error) : redirect()->back()->withInput()->with('error', $error);
        }

        // Sabit QR yenileme: sabit kalan bir lokasyonun KODU degisiyorsa kurum-bazli limite tabidir.
        $isFixedRegen = $existing
            && $data['qr_mode'] === 'fixed'
            && ($existing['qr_mode'] ?? 'fixed') === 'fixed'
            && $code !== $existing['code'];

        $settings = new \App\Models\SettingModel();
        $limit    = fixed_qr_regen_limit();
        $used     = (int) $settings->getValue('fixed_qr_regen_used', '0');

        if ($isFixedRegen && $limit > 0 && $used >= $limit) {
            $error = 'Sabit QR yenileme limiti doldu (' . $used . '/' . $limit . '). Yeni kod tanımlanamaz; mevcut kod korunuyor.';

            return $this->wantsJson() ? $this->jsonError($error) : redirect()->back()->withInput()->with('error', $error);
        }

        // Dinamik moda gecis: kapi ekrani (kiosk) imzasi yoksa uret.
        if ($data['qr_mode'] === 'dynamic' && empty($existing['token_secret'])) {
            $data['token_secret'] = bin2hex(random_bytes(16));
        }

        (new LocationModel())->update($id, $data);

        if ($isFixedRegen) {
            $settings->setValue('fixed_qr_regen_used', (string) ($used + 1));
        }

        $msg = 'Lokasyon güncellendi. Kod: ' . $code;
        if ($isFixedRegen && $limit > 0) {
            $msg .= ' · Sabit QR yenileme: ' . ($used + 1) . '/' . $limit;
        }

        return $this->wantsJson() ? $this->jsonOk(site_url('admin/locations'), $msg) : redirect()->to('/admin/locations')->with('message', $msg);
    }

    public function delete(int $id)
    {
        (new LocationModel())->delete($id);

        return redirect()->to('/admin/locations')->with('message', 'Lokasyon silindi.');
    }

    public function qr(int $id)
    {
        $location = (new LocationModel())->find($id);
        if ($location === null) {
            return redirect()->to('/admin/locations')->with('error', 'Lokasyon bulunamadı.');
        }

        return view('admin/locations/qr', ['location' => $location]);
    }

    public function token(int $id)
    {
        $location = (new LocationModel())->find($id);
        if ($location === null) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'not found']);
        }

        $base = rtrim(base_url(), '/') . '/q/' . $location['code'];

        if (qr_effective_mode($location['qr_mode']) === 'dynamic') {
            $token = (new DynamicQr())->issue((int) $location['id']);

            return $this->response->setJSON(['url' => $base . '?t=' . $token, 'ttl' => DynamicQr::TTL_SECONDS]);
        }

        return $this->response->setJSON(['url' => $base, 'ttl' => 0]);
    }

    private function validateLocation(string $code, ?int $id): ?string
    {
        if (trim((string) $this->request->getPost('name')) === '') {
            return 'Ad alanı zorunlu.';
        }
        if ($code === '') {
            return 'Geçerli bir kod gir (örn. ana-giris). Kod yalnızca harf, rakam ve tire içerebilir.';
        }
        if (strlen($code) > 50) {
            return 'Kod çok uzun (en fazla 50 karakter).';
        }
        $existing = (new LocationModel())->where('code', $code)->first();
        if ($existing !== null && (int) $existing['id'] !== (int) $id) {
            return 'Bu kod zaten kullanılıyor: ' . $code;
        }

        return null;
    }

    private function payload(string $code): array
    {
        $lat = $this->request->getPost('geo_lat');
        $lng = $this->request->getPost('geo_lng');
        $rad = $this->request->getPost('geo_radius_m');

        return [
            'code'         => $code,
            'name'         => (string) $this->request->getPost('name'),
            'qr_mode'      => (qr_dynamic_enabled() && $this->request->getPost('qr_mode') === 'dynamic') ? 'dynamic' : 'fixed',
            'geo_lat'      => is_numeric($lat) ? $lat : null,
            'geo_lng'      => is_numeric($lng) ? $lng : null,
            'geo_radius_m' => is_numeric($rad) ? (int) $rad : null,
            'enforce_geo'  => $this->request->getPost('enforce_geo') ? 1 : 0,
        ];
    }

    private function regenInfo(): array
    {
        return [
            'limit' => fixed_qr_regen_limit(),
            'used'  => (int) (new \App\Models\SettingModel())->getValue('fixed_qr_regen_used', '0'),
        ];
    }

    private function activeLocationCount(): int
    {
        return (new LocationModel())->where('is_active', 1)->countAllResults();
    }

    /** Lisans aktif-lokasyon limiti doluysa hata mesaji, degilse null. */
    private function locationLimitError(): ?string
    {
        $limit = max_locations();
        if ($limit <= 0) {
            return null;
        }
        if ($this->activeLocationCount() >= $limit) {
            return 'Lisans lokasyon limiti doldu (' . $this->activeLocationCount() . '/' . $limit . '). Yeni lokasyon için mevcut birini pasif yap/sil ya da paketi yükselt.';
        }

        return null;
    }
}
