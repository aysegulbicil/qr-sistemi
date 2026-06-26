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
        if (! $this->validate(['code' => 'required|max_length[50]|is_unique[locations.code]', 'name' => 'required'])) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $data              = $this->payload();
        $data['token_secret'] = $data['qr_mode'] === 'dynamic' ? bin2hex(random_bytes(16)) : null;
        $data['is_active']    = 1;
        (new LocationModel())->insert($data);

        return redirect()->to('/admin/locations')->with('message', 'Lokasyon eklendi.');
    }

    public function update(int $id)
    {
        if (! $this->validate(['code' => "required|max_length[50]|is_unique[locations.code,id,{$id}]", 'name' => 'required'])) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $data              = $this->payload();
        $data['is_active'] = $this->request->getPost('is_active') ? 1 : 0;
        (new LocationModel())->update($id, $data);

        return redirect()->to('/admin/locations')->with('message', 'Lokasyon güncellendi.');
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

        if ($location['qr_mode'] === 'dynamic') {
            $token = (new DynamicQr())->issue((int) $location['id']);

            return $this->response->setJSON(['url' => $base . '?t=' . $token, 'ttl' => DynamicQr::TTL_SECONDS]);
        }

        return $this->response->setJSON(['url' => $base, 'ttl' => 0]);
    }

    private function payload(): array
    {
        $lat = $this->request->getPost('geo_lat');
        $lng = $this->request->getPost('geo_lng');
        $rad = $this->request->getPost('geo_radius_m');

        return [
            'code'         => (string) $this->request->getPost('code'),
            'name'         => (string) $this->request->getPost('name'),
            'qr_mode'      => $this->request->getPost('qr_mode') === 'dynamic' ? 'dynamic' : 'fixed',
            'geo_lat'      => is_numeric($lat) ? $lat : null,
            'geo_lng'      => is_numeric($lng) ? $lng : null,
            'geo_radius_m' => is_numeric($rad) ? (int) $rad : null,
            'enforce_geo'  => $this->request->getPost('enforce_geo') ? 1 : 0,
        ];
    }
}
