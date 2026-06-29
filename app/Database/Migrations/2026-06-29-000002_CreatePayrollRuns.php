<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePayrollRuns extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'period_year'      => ['type' => 'INT', 'constraint' => 4],
            'period_month'     => ['type' => 'INT', 'constraint' => 2],
            'present_days'     => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            'expected_days'    => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            'missing_days'     => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            'leave_days'       => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            'late_minutes'     => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'overtime_minutes' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'worked_minutes'   => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'salary_type'      => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'monthly'],
            'salary_amount'    => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'base_salary'      => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'overtime_pay'     => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'overtime_mult'    => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 1.5],
            'advances_total'   => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'deductions_total' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'net_pay'          => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'currency'         => ['type' => 'VARCHAR', 'constraint' => 8, 'null' => true],
            'generated_by'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'generated_at'     => ['type' => 'DATETIME', 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'period_year', 'period_month']);
        $this->forge->addKey(['period_year', 'period_month']);
        $this->forge->createTable('payroll_runs', true, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('payroll_runs', true);
    }
}
