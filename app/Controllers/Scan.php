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
        $location = (new LocationModel())->findByCode($code);

        if ($location === null || ! $location['is_active']) {
            return view('scan_error', ['message' => 'Unknown or inactive QR location.']);
        }

        // Dynamic QR: a valid, unexpired, single-use token must be present.
        if (qr_effective_mode($location['qr_mode']) === 'dynamic') {
            $token = (string) $this->request->getGet('t');
            if ($token === '' || ! (new DynamicQr())->consume($token, (int) $location['id'])) {
                return view('scan_error', [
                    'message' => 'This QR code has expired. Please scan the current code on the screen again.',
                ]);
            }
        }

        // Remember the validated scan so the dashboard can offer Check in / out.
        session()->set('scan_context', [
            'location_id'   => (int) $location['id'],
            'location_name' => $location['name'],
            'at'            => date('Y-m-d H:i:s'),
        ]);

        // The auth filter sends guests to login first, then back here.
        return redirect()->to('/dashboard');
    }
}
