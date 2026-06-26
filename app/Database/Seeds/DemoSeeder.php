<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run()
    {
        // Idempotent: demo veri zaten varsa tekrar ekleme
        if ($this->db->table('settings')->where('setting_key', 'company_name')->countAllResults() > 0) {
            echo "Demo veri zaten mevcut, atlanıyor.\n";
            return;
        }

        $now = date('Y-m-d H:i:s');

        $settings = [
            'company_name' => 'Demo Şirketi',
            'work_mode'    => 'fixed',
            'fixed_start'  => '08:00',
            'fixed_end'    => '18:00',
            'grace_in'     => '5',
            'grace_out'    => '0',
        ];
        foreach ($settings as $key => $value) {
            $this->db->table('settings')->insert(['setting_key' => $key, 'setting_value' => $value, 'created_at' => $now, 'updated_at' => $now]);
        }

        $this->db->table('shifts')->insert([
            'name' => 'Gündüz Vardiyası', 'start_time' => '08:00:00', 'end_time' => '18:00:00',
            'grace_in_minutes' => 5, 'grace_out_minutes' => 0, 'crosses_midnight' => 0,
            'workdays' => '1,2,3,4,5', 'created_at' => $now, 'updated_at' => $now,
        ]);

        $hash  = password_hash('password', PASSWORD_DEFAULT);
        $users = [
            ['employee_code' => 'ADM-001', 'full_name' => 'Admin User',   'username' => 'admin',  'role' => 'admin'],
            ['employee_code' => 'EMP-001', 'full_name' => 'Ayse Yilmaz',  'username' => 'ayse',   'role' => 'employee'],
            ['employee_code' => 'EMP-002', 'full_name' => 'Mehmet Demir', 'username' => 'mehmet', 'role' => 'employee'],
        ];
        foreach ($users as $u) {
            $this->db->table('users')->insert(array_merge($u, [
                'password_hash' => $hash, 'shift_id' => 1, 'is_active' => 1,
                'created_at' => $now, 'updated_at' => $now,
            ]));
        }

        $this->db->table('locations')->insert([
            'code' => 'main-gate', 'name' => 'Ana Giriş', 'qr_mode' => 'fixed',
            'token_secret' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }
}
