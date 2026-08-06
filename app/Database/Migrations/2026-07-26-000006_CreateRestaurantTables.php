<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRestaurantTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'auto_increment' => true],
            'table_number' => ['type' => 'VARCHAR', 'constraint' => 20],
            'capacity'     => ['type' => 'INT', 'default' => 4],
            'status'       => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'available'],
            'deleted_at'   => ['type' => 'TIMESTAMPTZ', 'null' => true],
            'created_at'   => ['type' => 'TIMESTAMPTZ', 'null' => true],
            'updated_at'   => ['type' => 'TIMESTAMPTZ', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('table_number');
        $this->forge->createTable('restaurant_tables');
        $this->db->query("ALTER TABLE restaurant_tables ADD CONSTRAINT chk_tables_status CHECK (status IN ('available','occupied'))");
    }

    public function down()
    {
        $this->forge->dropTable('restaurant_tables');
    }
}
