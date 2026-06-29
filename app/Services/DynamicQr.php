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

        // Not: onceki tokenlar ARTIK aninda iptal edilmez; gecerlilik yalnizca TTL ile sinirli.
        // Boylece ekranda gorunen / az once okutulan kod rotasyon yuzunden erken olmez.
        $model->insert([
            'location_id' => $locationId,
            'token'       => $token,
            'issued_at'   => date('Y-m-d H:i:s', $now),
            'expires_at'  => date('Y-m-d H:i:s', $now + self::TTL_SECONDS),
        ]);

        return $token;
    }

    /**
     * Tarama aninda DOGRULAR ama tuketmez. Tuketim punch (POST) aninda yapilir;
     * boylece link onizlemesi (iOS Safari vb.) GET ile tokeni yakmaz.
     */
    public function validate(string $token, int $locationId): ?array
    {
        return (new QrTokenModel())->findValid($token, $locationId);
    }

    /** Punch aninda tek-kullanimlik tuketim. true: bu cagri tuketti; false: zaten kullanilmis. */
    public function markUsed(int $tokenId): bool
    {
        return (new QrTokenModel())->markUsedById($tokenId);
    }

    /** Teshis: dogrulama neden basarisiz oldu? missing|expired|used|ok */
    public function failureReason(string $token, int $locationId): string
    {
        return (new QrTokenModel())->classifyFailure($token, $locationId);
    }
}
