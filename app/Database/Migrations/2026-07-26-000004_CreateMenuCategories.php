<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMenuCategories extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'auto_increment' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 100],
            'description' => ['type' => 'TEXT', 'null' => true],
            'status'      => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'deleted_at'  => ['type' => 'TIMESTAMPTZ', 'null' => true],
            'created_at'  => ['type' => 'TIMESTAMPTZ', 'null' => true],
            'updated_at'  => ['type' => 'TIMESTAMPTZ', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('menu_categories');
        $this->db->query("ALTER TABLE menu_categories ADD CONSTRAINT chk_menu_categories_status CHECK (status IN ('active','inactive'))");
    }

    public function down()
    {
        $this->forge->dropTable('menu_categories');
    }
}
