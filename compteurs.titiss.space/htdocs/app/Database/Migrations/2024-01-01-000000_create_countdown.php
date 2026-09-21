<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCountdown extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'        => ['type' => 'INT', 'auto_increment' => true],
            'countdown_date' => ['type' => 'DATETIME'],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('countdown');
    }

    public function down(): void
    {
        $this->forge->dropTable('countdown');
    }
}