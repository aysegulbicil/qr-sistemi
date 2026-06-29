<?php

/**
 * Lisans / paket bayragi yardimcilari.
 *
 * "Dinamik QR" ozelligi satis paketine baglidir. .env icindeki
 * `feature.qrDynamic` true ise panelde Dinamik secenegi acilir;
 * tanimli degilse kurulum yalnizca Sabit QR ile calisir.
 */

if (! function_exists('qr_dynamic_enabled')) {
    /** Dinamik QR ozelligi bu kurulumda acik mi? */
    function qr_dynamic_enabled(): bool
    {
        return filter_var(env('feature.qrDynamic', false), FILTER_VALIDATE_BOOLEAN);
    }
}

if (! function_exists('qr_effective_mode')) {
    /**
     * Lokasyonun gercekte gecerli QR modu. Kayitli mod 'dynamic' olsa bile
     * ozellik kapaliysa 'fixed' dondurur.
     */
    function qr_effective_mode(?string $stored): string
    {
        return ($stored === 'dynamic' && qr_dynamic_enabled()) ? 'dynamic' : 'fixed';
    }
}

if (! function_exists('fixed_qr_regen_limit')) {
    /** Kurum basina toplam sabit-QR yenileme ust siniri. 0 = sinirsiz (kapali). */
    function fixed_qr_regen_limit(): int
    {
        return (int) env('feature.fixedQrRegenLimit', 0);
    }
}

if (! function_exists('max_locations')) {
    /** Bu kurulum (kurum) icin izinli AKTIF lokasyon (QR) sayisi. 0 = sinirsiz. Lisans/.env ile belirlenir, admin paneline kapalidir. */
    function max_locations(): int
    {
        return (int) env('feature.maxLocations', 0);
    }
}
