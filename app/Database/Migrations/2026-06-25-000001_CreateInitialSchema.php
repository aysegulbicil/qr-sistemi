<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInitialSchema extends Migration
{
    public function up(): void
    {
        // ---- settings (key/value company configuration) ----
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'setting_key'   => ['type' => 'VARCHAR', 'constraint' => 100],
            'setting_value' => ['type' => 'TEXT', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('setting_key');
        $this->forge->createTable('settings', true, ['ENGINE' => 'InnoDB']);

        // ---- shifts ----
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'              => ['type' => 'VARCHAR', 'constraint' => 100],
            'start_time'        => ['type' => 'TIME'],
            'end_time'          => ['type' => 'TIME'],
            'grace_in_minutes'  => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            'grace_out_minutes' => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            'crosses_midnight'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'workdays'          => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => '1,2,3,4,5'],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('shifts', true, ['ENGINE' => 'InnoDB']);

        // ---- users (employees + admins; everyone signs in) ----
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_code' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'full_name'     => ['type' => 'VARCHAR', 'constraint' => 150],
            'email'         => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'username'      => ['type' => 'VARCHAR', 'constraint' => 100],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'role'          => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'employee'],
            'shift_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'is_active'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('username');
        $this->forge->addKey('shift_id');
        $this->forge->createTable('users', true, ['ENGINE' => 'InnoDB']);

        // ---- locations (QR endpoints) ----
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'code'         => ['type' => 'VARCHAR', 'constraint' => 50],
            'name'         => ['type' => 'VARCHAR', 'constraint' => 150],
            'qr_mode'      => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'fixed'],
            'token_secret' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'is_active'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->createTable('locations', true, ['ENGINE' => 'InnoDB']);

        // ---- qr_tokens (dynamic mode, single-use + short lived) ----
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'location_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'token'       => ['type' => 'VARCHAR', 'constraint' => 64],
            'issued_at'   => ['type' => 'DATETIME'],
            'expires_at'  => ['type' => 'DATETIME'],
            'used_at'     => ['type' => 'DATETIME', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('token');
        $this->forge->addKey('location_id');
        $this->forge->createTable('qr_tokens', true, ['ENGINE' => 'InnoDB']);

        // ---- attendance_logs ----
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'location_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'type'        => ['type' => 'VARCHAR', 'constraint' => 3], // 'in' | 'out'
            'event_at'    => ['type' => 'DATETIME'],
            'source'      => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'qr'],
            'ip_address'  => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'qr_token_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'note'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'event_at']);
        $this->forge->createTable('attendance_logs', true, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('attendance_logs', true);
        $this->forge->dropTable('qr_tokens', true);
        $this->forge->dropTable('locations', true);
        $this->forge->dropTable('users', true);
        $this->forge->dropTable('shifts', true);
        $this->forge->dropTable('settings', true);
    }
}
