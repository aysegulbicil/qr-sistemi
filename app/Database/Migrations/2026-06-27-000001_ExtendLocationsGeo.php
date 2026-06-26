<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ExtendLocationsGeo extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('locations', [
            'geo_lat'      => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true, 'after' => 'token_secret'],
            'geo_lng'      => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true, 'after' => 'geo_lat'],
            'geo_radius_m' => ['type' => 'INT', 'constraint' => 6, 'null' => true, 'after' => 'geo_lng'],
            'enforce_geo'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'geo_radius_m'],
        ]);
    }

    public function down(): void
    {
        foreach (['geo_lat', 'geo_lng', 'geo_radius_m', 'enforce_geo'] as $col) {
            $this->forge->dropColumn('locations', $col);
        }
    }
}
