<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdvances extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'type'         => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'advance'], // advance | deduction
            'amount'       => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'reason'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'period_year'  => ['type' => 'INT', 'constraint' => 4],
            'period_month' => ['type' => 'INT', 'constraint' => 2],
            'created_by'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'period_year', 'period_month']);
        $this->forge->createTable('advances', true, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('advances', true);
    }
}
