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
            'locations' => (new LocationModel())->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    public function new()
    {
        return view('admin/locations/form', ['location' => null]);
    }

    public function edit(int $id)
    {
        $location = (new LocationModel())->find($id);
        if ($location === null) {
            return redirect()->to('/admin/locations')->with('error', 'Lokasyon bulunamadı.');
        }

        return view('admin/locations/form', ['location' => $location]);
    }

    public function create()
    {
        $code = slugify_code((string) $this->request->getPost('code'));
        if ($error = $this->validateLocation($code, null)) {
            return redirect()->back()->withInput()->with('error', $error);
        }

        $data                 = $this->payload($code);
        $data['token_secret'] = $data['qr_mode'] === 'dynamic' ? bin2hex(random_bytes(16)) : null;
        $data['is_active']    = 1;
        (new LocationModel())->insert($data);

        return redirect()->to('/admin/locations')->with('message', 'Lokasyon eklendi. Kod: ' . $code);
    }

    public function update(int $id)
    {
        $code = slugify_code((string) $this->request->getPost('code'));
        if ($error = $this->validateLocation($code, $id)) {
            return redirect()->back()->withInput()->with('error', $error);
        }

        $data              = $this->payload($code);
        $data['is_active'] = $this->request->getPost('is_active') ? 1 : 0;
        (new LocationModel())->update($id, $data);

        return redirect()->to('/admin/locations')->with('message', 'Lokasyon güncellendi. Kod: ' . $code);
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
}
