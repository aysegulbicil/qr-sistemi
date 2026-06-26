<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ExtendUsersHrFields extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'department_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'shift_id'],
            'position_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'department_id'],
            'salary_type'       => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'monthly', 'after' => 'position_id'],
            'salary_amount'     => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'salary_type'],
            'employment_status' => ['type' => 'VARCHAR', 'constraint' => 12, 'default' => 'active', 'after' => 'salary_amount'],
            'phone'             => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true, 'after' => 'employment_status'],
            'contact_email'     => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'phone'],
            'address'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'contact_email'],
            'national_id'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'after' => 'address'],
            'iban'              => ['type' => 'VARCHAR', 'constraint' => 34, 'null' => true, 'after' => 'national_id'],
            'hire_date'         => ['type' => 'DATE', 'null' => true, 'after' => 'iban'],
            'birth_date'        => ['type' => 'DATE', 'null' => true, 'after' => 'hire_date'],
            'photo_path'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'birth_date'],
        ]);
    }

    public function down(): void
    {
        foreach (['department_id', 'position_id', 'salary_type', 'salary_amount', 'employment_status', 'phone', 'contact_email', 'address', 'national_id', 'iban', 'hire_date', 'birth_date', 'photo_path'] as $col) {
            $this->forge->dropColumn('users', $col);
        }
    }
}
