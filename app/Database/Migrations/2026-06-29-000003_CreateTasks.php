<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTasks extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'title'        => ['type' => 'VARCHAR', 'constraint' => 150],
            'description'  => ['type' => 'TEXT', 'null' => true],
            'priority'     => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'normal'],
            'status'       => ['type' => 'VARCHAR', 'constraint' => 12, 'default' => 'pending'],
            'due_date'     => ['type' => 'DATE', 'null' => true],
            'assigned_by'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'completed_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'status']);
        $this->forge->addKey('due_date');
        $this->forge->addKey('assigned_by');
        $this->forge->createTable('tasks', true, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('tasks', true);
    }
}
