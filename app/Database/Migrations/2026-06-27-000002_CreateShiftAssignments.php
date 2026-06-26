<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShiftAssignments extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'work_date'  => ['type' => 'DATE'],
            'shift_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'note'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'work_date']);
        $this->forge->addKey('shift_id');
        $this->forge->addKey('work_date');
        $this->forge->createTable('shift_assignments', true, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('shift_assignments', true);
    }
}
