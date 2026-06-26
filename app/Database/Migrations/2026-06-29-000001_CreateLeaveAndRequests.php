<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLeaveAndRequests extends Migration
{
    public function up(): void
    {
        // leave_types
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'is_paid'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('leave_types', true, ['ENGINE' => 'InnoDB']);

        $now = date('Y-m-d H:i:s');
        foreach ([['Yıllık İzin', 1], ['Mazeret İzni', 1], ['Hastalık İzni', 1], ['Ücretsiz İzin', 0]] as $t) {
            $this->db->table('leave_types')->insert(['name' => $t[0], 'is_paid' => $t[1], 'created_at' => $now, 'updated_at' => $now]);
        }

        // leave_requests
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'leave_type_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'start_date'    => ['type' => 'DATE'],
            'end_date'      => ['type' => 'DATE'],
            'days'          => ['type' => 'DECIMAL', 'constraint' => '5,1', 'default' => 0],
            'reason'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'        => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'pending'],
            'approver_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'decided_at'    => ['type' => 'DATETIME', 'null' => true],
            'note'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'status']);
        $this->forge->createTable('leave_requests', true, ['ENGINE' => 'InnoDB']);

        // advance_requests
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'amount'       => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'reason'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'       => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'pending'],
            'approver_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'decided_at'   => ['type' => 'DATETIME', 'null' => true],
            'period_year'  => ['type' => 'INT', 'constraint' => 4],
            'period_month' => ['type' => 'INT', 'constraint' => 2],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'status']);
        $this->forge->createTable('advance_requests', true, ['ENGINE' => 'InnoDB']);

        // notifications
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'type'       => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'message'    => ['type' => 'VARCHAR', 'constraint' => 255],
            'url'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'read_at'    => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'read_at']);
        $this->forge->createTable('notifications', true, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('notifications', true);
        $this->forge->dropTable('advance_requests', true);
        $this->forge->dropTable('leave_requests', true);
        $this->forge->dropTable('leave_types', true);
    }
}
