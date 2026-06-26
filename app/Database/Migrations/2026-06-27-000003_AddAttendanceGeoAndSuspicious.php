<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAttendanceGeoAndSuspicious extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('attendance_logs', [
            'geo_lat'           => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true, 'after' => 'user_agent'],
            'geo_lng'           => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true, 'after' => 'geo_lat'],
            'distance_m'        => ['type' => 'INT', 'constraint' => 7, 'null' => true, 'after' => 'geo_lng'],
            'is_suspicious'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'distance_m'],
            'suspicious_reason' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'is_suspicious'],
        ]);

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'location_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'type'        => ['type' => 'VARCHAR', 'constraint' => 4, 'null' => true],
            'reason'      => ['type' => 'VARCHAR', 'constraint' => 150],
            'geo_lat'     => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
            'geo_lng'     => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
            'distance_m'  => ['type' => 'INT', 'constraint' => 7, 'null' => true],
            'ip_address'  => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('created_at');
        $this->forge->createTable('suspicious_events', true, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        foreach (['geo_lat', 'geo_lng', 'distance_m', 'is_suspicious', 'suspicious_reason'] as $col) {
            $this->forge->dropColumn('attendance_logs', $col);
        }
        $this->forge->dropTable('suspicious_events', true);
    }
}
