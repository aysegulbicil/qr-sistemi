<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table         = 'settings';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['setting_key', 'setting_value'];

    public const DEFAULTS = [
        'company_name'        => 'My Company',
        'work_mode'           => 'fixed',
        'fixed_start'         => '08:00',
        'fixed_end'           => '18:00',
        'grace_in'            => '5',
        'grace_out'           => '0',
        'currency'            => '₺',
        'daily_hours'         => '9',
        'workdays_per_month'  => '22',
        'overtime_multiplier' => '1.5',
    ];

    public function getValue(string $key, ?string $default = null): ?string
    {
        $row = $this->where('setting_key', $key)->first();
        if ($row !== null) {
            return $row['setting_value'];
        }

        return $default ?? (self::DEFAULTS[$key] ?? null);
    }

    public function setValue(string $key, ?string $value): void
    {
        $existing = $this->where('setting_key', $key)->first();
        if ($existing !== null) {
            $this->update($existing['id'], ['setting_value' => $value]);
        } else {
            $this->insert(['setting_key' => $key, 'setting_value' => $value]);
        }
    }

    public function allMerged(): array
    {
        $stored = [];
        foreach ($this->findAll() as $row) {
            $stored[$row['setting_key']] = $row['setting_value'];
        }

        return array_merge(self::DEFAULTS, $stored);
    }
}
