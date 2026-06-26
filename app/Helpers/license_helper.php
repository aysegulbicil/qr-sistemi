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
