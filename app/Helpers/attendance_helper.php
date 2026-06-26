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
