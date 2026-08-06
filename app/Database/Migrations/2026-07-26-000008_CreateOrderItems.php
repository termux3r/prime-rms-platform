<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrderItems extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'auto_increment' => true],
            'order_id'     => ['type' => 'INT'],
            'menu_item_id' => ['type' => 'INT'],
            'quantity'     => ['type' => 'INT'],
            'unit_price'   => ['type' => 'NUMERIC', 'constraint' => '10,2'],
            'subtotal'     => ['type' => 'NUMERIC', 'constraint' => '10,2'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('order_id', 'orders', 'id', false, 'CASCADE');
        $this->forge->addForeignKey('menu_item_id', 'menu_items', 'id');
        $this->forge->createTable('order_items');

        $this->db->query("ALTER TABLE order_items ADD CONSTRAINT chk_order_items_qty CHECK (quantity > 0)");
        $this->db->query("CREATE INDEX idx_order_items_order ON order_items(order_id)");
        $this->db->query("CREATE INDEX idx_order_items_menu_item ON order_items(menu_item_id)");
    }

    public function down()
    {
        $this->forge->dropTable('order_items');
    }
}
