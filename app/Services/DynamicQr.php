<?php

namespace App\Services;

use App\Models\QrTokenModel;

/**
 * Issues and verifies short-lived, single-use tokens for dynamic (rotating) QR codes.
 * The displayed QR refreshes every ~30s; tokens live slightly longer to absorb clock skew.
 */
class DynamicQr
{
    public const TTL_SECONDS = 45;

    public function issue(int $locationId): string
    {
        $token = bin2hex(random_bytes(16));
        $now   = time();
        $model = new QrTokenModel();

        // Yeni kod uretilince onceki kullanilmamis kodlar gecersiz olur ->
        // sadece ekrandaki guncel QR ile giris yapilabilir.
        $model->invalidateForLocation($locationId);

        $model->insert([
            'location_id' => $locationId,
            'token'       => $token,
            'issued_at'   => date('Y-m-d H:i:s', $now),
            'expires_at'  => date('Y-m-d H:i:s', $now + self::TTL_SECONDS),
        ]);

        return $token;
    }

    /** Returns true and consumes the token if valid; false otherwise. */
    public function consume(string $token, int $locationId): bool
    {
        $model = new QrTokenModel();
        $row   = $model->findValid($token, $locationId);

        if ($row === null) {
            return false;
        }

        $model->update($row['id'], ['used_at' => date('Y-m-d H:i:s')]);

        return true;
    }
}
