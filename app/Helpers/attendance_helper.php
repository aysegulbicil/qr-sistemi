<?php

/**
 * Attendance / personnel / payroll view helpers.
 */

if (! function_exists('minutes_to_hm')) {
    function minutes_to_hm(int $minutes): string
    {
        $minutes = max(0, $minutes);
        $h       = intdiv($minutes, 60);
        $m       = $minutes % 60;

        return $h > 0 ? ($h . ' sa ' . $m . ' dk') : ($m . ' dk');
    }
}

if (! function_exists('hhmm')) {
    function hhmm(?string $datetime): string
    {
        return $datetime ? substr($datetime, 11, 5) : '—';
    }
}

if (! function_exists('status_label')) {
    function status_label(string $status): array
    {
        return match ($status) {
            'present'    => ['Tam', 'badge-green'],
            'incomplete' => ['Eksik', 'badge-amber'],
            default      => ['Yok', 'badge-grey'],
        };
    }
}

if (! function_exists('emp_status_badge')) {
    function emp_status_badge(string $status): array
    {
        return match ($status) {
            'active'     => ['Aktif', 'badge-green'],
            'terminated' => ['Ayrıldı', 'badge-grey'],
            default      => ['Pasif', 'badge-amber'],
        };
    }
}

if (! function_exists('salary_type_label')) {
    function salary_type_label(string $type): string
    {
        return match ($type) {
            'daily'  => 'Günlük',
            'hourly' => 'Saatlik',
            default  => 'Aylık',
        };
    }
}

if (! function_exists('money')) {
    function money(float $amount, string $currency = '₺'): string
    {
        return number_format($amount, 2, ',', '.') . ' ' . $currency;
    }
}

if (! function_exists('initials')) {
    function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $a     = mb_substr($parts[0] ?? '', 0, 1);
        $b     = mb_substr($parts[count($parts) - 1] ?? '', 0, 1);

        return mb_strtoupper($a . ($b !== $a ? $b : ''));
    }
}

if (! function_exists('tr_date')) {
    function tr_date(?string $date): string
    {
        if (! $date) {
            return '—';
        }
        $months = [1 => 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
        $ts     = strtotime($date);

        return date('j', $ts) . ' ' . $months[(int) date('n', $ts)] . ' ' . date('Y', $ts);
    }
}

if (! function_exists('scan_is_fresh')) {
    /**
     * Bir okutma baglami giris/cikis icin hala taze mi? (varsayilan 180 sn).
     */
    function scan_is_fresh(?array $scan, int $maxAgeSeconds = 180): bool
    {
        if (empty($scan['at'])) {
            return false;
        }
        $t = strtotime((string) $scan['at']);

        return $t !== false && (time() - $t) <= $maxAgeSeconds;
    }
}

if (! function_exists('slugify_code')) {
    /**
     * URL-guvenli kod uretir: Turkce harfleri sadelestirir, kucuk harfe cevirir,
     * harf/rakam disini tireye donusturur. "Giris Kapisi" -> "giris-kapisi".
     */
    function slugify_code(string $raw): string
    {
        $map = ['ş'=>'s','Ş'=>'s','ı'=>'i','İ'=>'i','ğ'=>'g','Ğ'=>'g','ç'=>'c','Ç'=>'c','ö'=>'o','Ö'=>'o','ü'=>'u','Ü'=>'u'];
        $s   = strtr($raw, $map);
        $s   = mb_strtolower($s, 'UTF-8');
        $s   = preg_replace('/[^a-z0-9]+/', '-', $s);
        return trim((string) $s, '-');
    }
}
