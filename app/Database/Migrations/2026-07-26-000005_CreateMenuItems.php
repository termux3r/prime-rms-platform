<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMenuItems extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'auto_increment' => true],
            'category_id' => ['type' => 'INT'],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 150],
            'description' => ['type' => 'TEXT', 'null' => true],
            'price'       => ['type' => 'NUMERIC', 'constraint' => '10,2'],
            'image'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'      => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'deleted_at'  => ['type' => 'TIMESTAMPTZ', 'null' => true],
            'created_at'  => ['type' => 'TIMESTAMPTZ', 'null' => true],
            'updated_at'  => ['type' => 'TIMESTAMPTZ', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('category_id', 'menu_categories', 'id', false, 'RESTRICT');
        $this->forge->createTable('menu_items');
        $this->db->query("ALTER TABLE menu_items ADD CONSTRAINT chk_menu_items_status CHECK (status IN ('active','inactive'))");
        $this->db->query("ALTER TABLE menu_items ADD CONSTRAINT chk_menu_items_price CHECK (price >= 0)");
        $this->db->query("CREATE INDEX idx_menu_items_category ON menu_items(category_id)");
    }

    public function down()
    {
        $this->forge->dropTable('menu_items');
    }
}
